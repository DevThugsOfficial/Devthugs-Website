const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcPhpRoot = path.join(root, 'node_modules', '@libphp', 'almalinux-9-v85', 'native');
const bridgeRoot = path.join(root, 'api', '_phpbridge');
const phpDir = path.join(bridgeRoot, 'php');
const libDir = path.join(bridgeRoot, 'lib');
const phpBin = path.join(phpDir, 'php');
const distDir = path.join(root, 'dist');
const publicDir = path.join(root, 'public');

function log(...args) {
  console.log('[vercel-build]', ...args);
}

function chmodSafe(filePath) {
  try {
    if (fs.existsSync(filePath)) fs.chmodSync(filePath, 0o755);
  } catch (error) {
    log('chmod skipped:', filePath, error.message);
  }
}

function copyDir(src, dest) {
  if (!fs.existsSync(src)) return;
  fs.mkdirSync(dest, { recursive: true });

  for (const entry of fs.readdirSync(src, { withFileTypes: true })) {
    const from = path.join(src, entry.name);
    const to = path.join(dest, entry.name);

    if (entry.isSymbolicLink()) {
      log('Skipping symlink:', from);
      continue;
    }

    if (entry.isDirectory()) {
      copyDir(from, to);
      continue;
    }

    if (entry.isFile()) {
      fs.copyFileSync(from, to);
    }
  }
}

function patchPhpIni() {
  const iniPath = path.join(phpDir, 'php.ini');
  const modulesDir = path.join(phpDir, 'modules');
  let ini = fs.readFileSync(iniPath, 'utf8');
  ini = ini.replace(/extension_dir\s*=\s*.*/i, `extension_dir=${modulesDir.replace(/\\/g, '/')}`);
  fs.writeFileSync(iniPath, ini);
  log('Patched php.ini extension_dir ->', modulesDir);
}

function preparePhpBridge() {
  if (!fs.existsSync(srcPhpRoot)) {
    console.error('[vercel-build] Missing @libphp package at', srcPhpRoot);
    process.exit(1);
  }

  log('Copying PHP runtime into api/_phpbridge...');
  fs.rmSync(bridgeRoot, { recursive: true, force: true });
  copyDir(path.join(srcPhpRoot, 'php'), phpDir);
  copyDir(path.join(srcPhpRoot, 'lib'), libDir);
  chmodSafe(phpBin);
  chmodSafe(path.join(phpDir, 'php-cgi'));
  chmodSafe(path.join(phpDir, 'composer'));
  patchPhpIni();

  if (!fs.existsSync(phpBin) || !fs.existsSync(path.join(phpDir, 'php-cgi'))) {
    console.error('[vercel-build] PHP binaries were not copied correctly');
    process.exit(1);
  }
}

function prepareDist() {
  log('Preparing dist static assets...');
  fs.rmSync(distDir, { recursive: true, force: true });
  fs.mkdirSync(distDir, { recursive: true });
  copyDir(publicDir, distDir);
  fs.writeFileSync(path.join(distDir, '.vercel-static'), 'ok\n');
}

function ensureVendorOrComposer() {
  const autoload = path.join(root, 'vendor', 'autoload.php');
  if (fs.existsSync(autoload)) {
    log('Using existing vendor/autoload.php');
    return;
  }

  if (process.platform !== 'linux') {
    console.error('[vercel-build] vendor/ missing and cannot run Linux PHP Composer locally');
    process.exit(1);
  }

  log('Running Composer install with patched PHP...');
  const { spawnSync } = require('child_process');
  const composerBin = path.join(phpDir, 'composer');
  chmodSafe(composerBin);

  const phpCheck = spawnSync(phpBin, ['-c', path.join(phpDir, 'php.ini'), '-m'], {
    cwd: root,
    encoding: 'utf8',
    env: {
      ...process.env,
      PATH: `${phpDir}:${process.env.PATH || ''}`,
      LD_LIBRARY_PATH: `${libDir}:${process.env.LD_LIBRARY_PATH || ''}`,
    },
  });
  log('PHP modules sample:', String(phpCheck.stdout || '').split('\n').slice(0, 8).join(', '));
  if (phpCheck.status !== 0) {
    console.error(phpCheck.stderr || phpCheck.stdout || phpCheck.error);
    process.exit(1);
  }

  const result = spawnSync(
    phpBin,
    [
      '-c',
      path.join(phpDir, 'php.ini'),
      composerBin,
      'install',
      '--no-dev',
      '--no-interaction',
      '--prefer-dist',
      '--optimize-autoloader',
      '--ignore-platform-reqs',
      '--no-scripts',
      '--no-progress',
    ],
    {
      cwd: root,
      stdio: 'inherit',
      env: {
        ...process.env,
        PATH: `${phpDir}:${process.env.PATH || ''}`,
        LD_LIBRARY_PATH: `${libDir}:${process.env.LD_LIBRARY_PATH || ''}`,
        COMPOSER_ALLOW_SUPERUSER: '1',
        COMPOSER_HOME: path.join(root, '.composer'),
      },
    }
  );

  if (result.status !== 0) {
    console.error('[vercel-build] Composer install failed with status', result.status);
    process.exit(result.status || 1);
  }

  if (!fs.existsSync(autoload)) {
    console.error('[vercel-build] vendor/autoload.php still missing after Composer');
    process.exit(1);
  }
}

try {
  preparePhpBridge();
  prepareDist();
  ensureVendorOrComposer();
  log('Vercel build complete');
} catch (error) {
  console.error('[vercel-build] Fatal error:', error);
  process.exit(1);
}
