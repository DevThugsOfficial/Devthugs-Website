const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');
const { parse: parseUrl } = require('url');

const ROOT = path.join(__dirname, '..');
const PHP_DIR = path.join(__dirname, '_phpbridge', 'php');
const LIB_DIR = path.join(__dirname, '_phpbridge', 'lib');
const PHP_CGI = path.join(PHP_DIR, 'php-cgi');
const PHP_INI_SRC = path.join(PHP_DIR, 'php.ini');
const ENTRY = path.join(__dirname, 'laravel.php');

function ensureExecutable(filePath) {
  try {
    fs.chmodSync(filePath, 0o755);
  } catch (_) {}
}

function writeRuntimeIni() {
  const modulesDir = path.join(PHP_DIR, 'modules');
  const runtimeIni = path.join('/tmp', 'devthugs-php.ini');
  let ini = fs.readFileSync(PHP_INI_SRC, 'utf8');
  ini = ini.replace(/extension_dir\s*=\s*.*/i, `extension_dir=${modulesDir}`);
  ini += `
display_errors=1
display_startup_errors=1
html_errors=1
log_errors=1
error_reporting=E_ALL
cgi.force_redirect=0
cgi.fix_pathinfo=1
`;
  fs.writeFileSync(runtimeIni, ini);
  return runtimeIni;
}

function resolveRequestUri(req) {
  const rawUrl = req.url || '/';
  const { pathname, query } = parseUrl(rawUrl, true);

  // Prefer original browser path passed by rewrite (?__path=/contact)
  if (query && typeof query.__path === 'string' && query.__path.startsWith('/')) {
    return query.__path;
  }

  const headerCandidates = [
    req.headers['x-invoke-path'],
    req.headers['x-matched-path'],
    req.headers['x-forwarded-uri'],
    req.headers['x-vercel-forwarded-path'],
  ];

  for (const candidate of headerCandidates) {
    if (!candidate) continue;
    const value = Array.isArray(candidate) ? candidate[0] : String(candidate);
    if (value.startsWith('/') && !value.startsWith('/api')) {
      return value.split('?')[0] || '/';
    }
  }

  if (pathname && pathname !== '/api' && pathname !== '/api/index') {
    return pathname;
  }

  return '/';
}

function collectBody(req) {
  return new Promise((resolve, reject) => {
    const chunks = [];
    req.on('data', (chunk) => chunks.push(chunk));
    req.on('end', () => resolve(Buffer.concat(chunks)));
    req.on('error', reject);
  });
}

function buildCgiEnv(req, body) {
  const host = req.headers['x-forwarded-host'] || req.headers.host || 'localhost';
  const proto = (req.headers['x-forwarded-proto'] || 'https').toString().split(',')[0].trim();
  const rawUrl = req.url || '/';
  const parsed = parseUrl(rawUrl, true);
  const requestPath = resolveRequestUri(req);

  // Rebuild query string without the internal __path helper
  const forwardQuery = { ...(parsed.query || {}) };
  delete forwardQuery.__path;
  const queryString = Object.entries(forwardQuery)
    .flatMap(([key, value]) => {
      if (Array.isArray(value)) {
        return value.map((item) => `${encodeURIComponent(key)}=${encodeURIComponent(String(item))}`);
      }
      if (value == null) return [`${encodeURIComponent(key)}=`];
      return [`${encodeURIComponent(key)}=${encodeURIComponent(String(value))}`];
    })
    .join('&');

  const requestUri = queryString ? `${requestPath}?${queryString}` : requestPath;

  const clientIp = (
    req.headers['x-real-ip'] ||
    req.headers['x-vercel-forwarded-for'] ||
    req.headers['x-forwarded-for'] ||
    '127.0.0.1'
  )
    .toString()
    .split(',')[0]
    .trim();

  const env = {
    ...process.env,
    PATH: `${PHP_DIR}:${process.env.PATH || ''}`,
    LD_LIBRARY_PATH: `${LIB_DIR}:${process.env.LD_LIBRARY_PATH || ''}`,
    REDIRECT_STATUS: '200',
    SERVER_SOFTWARE: 'Vercel-Node-PHP',
    SERVER_NAME: String(host).split(':')[0],
    SERVER_PORT: proto === 'https' ? '443' : '80',
    SERVER_PROTOCOL: 'HTTP/1.1',
    GATEWAY_INTERFACE: 'CGI/1.1',
    REQUEST_METHOD: req.method || 'GET',
    REQUEST_URI: requestUri,
    QUERY_STRING: queryString,
    SCRIPT_NAME: '/index.php',
    SCRIPT_FILENAME: ENTRY,
    PATH_INFO: requestPath === '/' ? '' : requestPath,
    PATH_TRANSLATED: ENTRY,
    DOCUMENT_ROOT: ROOT,
    REMOTE_ADDR: clientIp,
    REMOTE_PORT: '443',
    HTTPS: proto === 'https' ? 'on' : '',
    HTTP_HOST: host,
    CONTENT_LENGTH: String(body.length),
    VERCEL: process.env.VERCEL || '1',
  };

  if (req.headers['content-type']) {
    env.CONTENT_TYPE = req.headers['content-type'];
  }

  for (const [key, value] of Object.entries(req.headers)) {
    if (value == null) continue;
    const headerName = 'HTTP_' + key.toUpperCase().replace(/-/g, '_');
    env[headerName] = Array.isArray(value) ? value.join(',') : String(value);
  }

  return env;
}

function parseCgiOutput(buffer) {
  const separator = Buffer.from('\r\n\r\n');
  let index = buffer.indexOf(separator);
  let headerSize = 4;
  if (index === -1) {
    const alt = Buffer.from('\n\n');
    index = buffer.indexOf(alt);
    headerSize = 2;
  }

  if (index === -1) {
    return { statusCode: 200, headers: { 'content-type': 'text/html; charset=utf-8' }, body: buffer };
  }

  const rawHeaders = buffer.slice(0, index).toString('utf8');
  const body = buffer.slice(index + headerSize);
  const headers = {};
  let statusCode = 200;

  for (const line of rawHeaders.split(/\r?\n/)) {
    if (!line.trim()) continue;
    const colon = line.indexOf(':');
    if (colon === -1) continue;
    const key = line.slice(0, colon).trim().toLowerCase();
    const value = line.slice(colon + 1).trim();
    if (key === 'status') {
      statusCode = parseInt(value, 10) || 200;
      continue;
    }
    headers[key] = value;
  }

  return { statusCode, headers, body };
}

function runPhpCgi(env, body) {
  return new Promise((resolve) => {
    ensureExecutable(PHP_CGI);
    ensureExecutable(path.join(PHP_DIR, 'php'));

    if (!fs.existsSync(PHP_CGI)) {
      resolve({
        statusCode: 500,
        headers: { 'content-type': 'text/plain; charset=utf-8' },
        body: Buffer.from(`PHP CGI binary missing at ${PHP_CGI}`),
      });
      return;
    }

    if (!fs.existsSync(ENTRY)) {
      resolve({
        statusCode: 500,
        headers: { 'content-type': 'text/plain; charset=utf-8' },
        body: Buffer.from(`Laravel entry missing at ${ENTRY}`),
      });
      return;
    }

    const runtimeIni = writeRuntimeIni();
    const php = spawn(PHP_CGI, ['-c', runtimeIni, ENTRY], {
      env,
      cwd: PHP_DIR,
      stdio: ['pipe', 'pipe', 'pipe'],
    });

    const stdout = [];
    const stderr = [];

    php.stdout.on('data', (chunk) => stdout.push(chunk));
    php.stderr.on('data', (chunk) => stderr.push(chunk));

    php.on('error', (error) => {
      resolve({
        statusCode: 500,
        headers: { 'content-type': 'text/plain; charset=utf-8' },
        body: Buffer.from(`Failed to start PHP CGI: ${error.message}`),
      });
    });

    php.on('close', (code) => {
      const errText = stderr.length ? Buffer.concat(stderr).toString('utf8') : '';
      if (errText) {
        console.error(errText);
      }

      if (code && code !== 0 && stdout.length === 0) {
        resolve({
          statusCode: 500,
          headers: { 'content-type': 'text/plain; charset=utf-8' },
          body: Buffer.from(`PHP CGI exited with code ${code}\n\n${errText}`),
        });
        return;
      }

      const raw = Buffer.concat(stdout);
      const parsed = parseCgiOutput(raw);
      if (!parsed.body || parsed.body.length === 0) {
        parsed.statusCode = parsed.statusCode >= 400 ? parsed.statusCode : 500;
        parsed.headers = { 'content-type': 'text/plain; charset=utf-8' };
        parsed.body = Buffer.from(
          [
            `Empty PHP response (exit ${code ?? 0}).`,
            '',
            `STDERR:`,
            errText || '(none)',
            '',
            `RAW STDOUT (${raw.length} bytes):`,
            raw.toString('utf8').slice(0, 4000) || '(none)',
            '',
            `PHP_CGI: ${PHP_CGI}`,
            `ENTRY: ${ENTRY}`,
            `PHP exists: ${fs.existsSync(PHP_CGI)}`,
            `ENTRY exists: ${fs.existsSync(ENTRY)}`,
            `vendor: ${fs.existsSync(path.join(ROOT, 'vendor', 'autoload.php'))}`,
          ].join('\n')
        );
      }
      resolve(parsed);
    });

    php.stdin.write(body);
    php.stdin.end();
  });
}

module.exports = async function handler(req, res) {
  try {
    if ((req.url || '').includes('__bridge=1')) {
      res.statusCode = 200;
      res.setHeader('content-type', 'text/plain; charset=utf-8');
      res.end(
        [
          'bridge-ok',
          `url=${req.url}`,
          `php=${PHP_CGI} exists=${fs.existsSync(PHP_CGI)}`,
          `entry=${ENTRY} exists=${fs.existsSync(ENTRY)}`,
          `vendor=${fs.existsSync(path.join(ROOT, 'vendor', 'autoload.php'))}`,
          `resolved=${resolveRequestUri(req)}`,
          `headers=${JSON.stringify(req.headers, null, 2)}`,
        ].join('\n')
      );
      return;
    }

    const body = ['POST', 'PUT', 'PATCH'].includes((req.method || 'GET').toUpperCase())
      ? await collectBody(req)
      : Buffer.alloc(0);

    const env = buildCgiEnv(req, body);
    const result = await runPhpCgi(env, body);

    res.statusCode = result.statusCode;

    // Force a browser-renderable content type (never let PHP CGI mark this as a download)
    const headers = { ...result.headers };
    delete headers['content-disposition'];
    delete headers['transfer-encoding'];

    const preview = result.body.toString('utf8', 0, Math.min(result.body.length, 200)).toLowerCase();
    const looksLikeHtml = preview.includes('<!doctype') || preview.includes('<html') || preview.includes('<head');
    const looksLikePlain = (headers['content-type'] || '').includes('text/plain');
    if (
      !looksLikePlain &&
      (looksLikeHtml ||
        !headers['content-type'] ||
        headers['content-type'].includes('octet-stream') ||
        headers['content-type'].includes('x-httpd-php'))
    ) {
      headers['content-type'] = 'text/html; charset=utf-8';
    }

    for (const [key, value] of Object.entries(headers)) {
      res.setHeader(key, value);
    }
    res.end(result.body);
  } catch (error) {
    console.error(error);
    res.statusCode = 500;
    res.setHeader('content-type', 'text/plain; charset=utf-8');
    res.end(`Node PHP bridge error: ${error.message}`);
  }
};
