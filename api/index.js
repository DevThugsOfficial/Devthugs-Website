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
  // Keep only extensions we need for Laravel + Supabase
  fs.writeFileSync(runtimeIni, ini);
  return runtimeIni;
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
  const { query } = parseUrl(rawUrl);

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
    REQUEST_URI: rawUrl,
    QUERY_STRING: query || '',
    SCRIPT_NAME: '/api/laravel.php',
    SCRIPT_FILENAME: ENTRY,
    PATH_TRANSLATED: ENTRY,
    DOCUMENT_ROOT: ROOT,
    HTTPS: proto === 'https' ? 'on' : '',
    HTTP_HOST: host,
    CONTENT_LENGTH: String(body.length),
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
      if (stderr.length) {
        console.error(Buffer.concat(stderr).toString('utf8'));
      }
      if (code && code !== 0 && stdout.length === 0) {
        resolve({
          statusCode: 500,
          headers: { 'content-type': 'text/plain; charset=utf-8' },
          body: Buffer.from(`PHP CGI exited with code ${code}`),
        });
        return;
      }
      resolve(parseCgiOutput(Buffer.concat(stdout)));
    });

    php.stdin.write(body);
    php.stdin.end();
  });
}

module.exports = async function handler(req, res) {
  try {
    const body = ['POST', 'PUT', 'PATCH'].includes((req.method || 'GET').toUpperCase())
      ? await collectBody(req)
      : Buffer.alloc(0);

    const env = buildCgiEnv(req, body);
    const result = await runPhpCgi(env, body);

    res.statusCode = result.statusCode;
    for (const [key, value] of Object.entries(result.headers)) {
      if (key === 'transfer-encoding') continue;
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
