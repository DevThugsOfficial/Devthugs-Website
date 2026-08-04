const { spawnSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcPhpRoot = path.join(root, 'node_modules', '@libphp', 'almalinux-9-v85', 'native');
const bridgeRoot = path.join(root, 'api', '_phpbridge');
const phpDir = path.join(bridgeRoot, 'php');
const libDir = path.join(bridgeRoot, 'lib');
const phpBin = path.join(phpDir, 'php');
const composerBin = path.join(phpDir, 'composer');
const distDir = path.join(root, 'dist');
const publicDir = path.join(root, 'public');

function log(...args) {
  console.log('[vercel-build]', ...args);
}

function chmodSafe(filePath) {
  try {
    if (fs.existsSync(filePath)) {
      fs.chmodSync(filePath, 0o755);
    }
  } catch (error) {
    log('chmod skipped:', filePath, error.message);
  }
}

function copyDir(src, dest) {
  if (!fs.existsSync(src)) {
    return;
  }

  fs.mkdirSync(dest, { recursive: true });

  for (const entry of fs.readdirSync(src, { withFileTypes: true })) {
    const from = path.join(src, entry.name);
    const to = path.join(dest, entry.name);

    // Skip broken Laravel public/storage symlink and other symlinks
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

function preparePhpBridge() {
  if (!fs.existsSync(srcPhpRoot)) {
    console.error('[vercel-build] Missing @libphp package at', srcPhpRoot);
    console.error('[vercel-build] node_modules listing:');
    try {
      console.error(fs.readdirSync(path.join(root, 'node_modules')).join(', '));
    } catch (_) {}
    process.exit(1);
  }

  log('Copying PHP runtime into api/_phpbridge...');
  fs.rmSync(bridgeRoot, { recursive: true, force: true });
  copyDir(path.join(srcPhpRoot, 'php'), phpDir);
  copyDir(path.join(srcPhpRoot, 'lib'), libDir);
  chmodSafe(phpBin);
  chmodSafe(path.join(phpDir, 'php-cgi'));
  chmodSafe(composerBin);

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

function runComposer() {
  log('Installing Composer dependencies with bundled PHP...');

  if (process.platform !== 'linux') {
    log(`Skipping Composer on platform=${process.platform}`);
    return;
  }

  if (!fs.existsSync(phpBin) || !fs.existsSync(composerBin)) {
    console.error('[vercel-build] PHP/Composer binary missing for Linux build');
    process.exit(1);
  }

  // Sanity check that the Linux PHP binary can start
  const phpCheck = spawnSync(phpBin, ['-v'], {
    cwd: root,
    encoding: 'utf8',
    env: {
      ...process.env,
      PATH: `${phpDir}:${process.env.PATH || ''}`,
      LD_LIBRARY_PATH: `${libDir}:${process.env.LD_LIBRARY_PATH || ''}`,
    },
  });

  if (phpCheck.status !== 0) {
    console.error('[vercel-build] Bundled PHP failed to start');
    console.error(phpCheck.stdout || '');
    console.error(phpCheck.stderr || '');
    console.error(phpCheck.error || '');
    process.exit(1);
  }

  log(String(phpCheck.stdout || '').split('\n')[0]);

  const result = spawnSync(
    phpBin,
    [
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

  if (result.error) {
    console.error('[vercel-build] Composer spawn error:', result.error);
    process.exit(1);
  }

  if (result.status !== 0) {
    console.error('[vercel-build] Composer install failed with status', result.status);
    process.exit(result.status || 1);
  }

  if (!fs.existsSync(path.join(root, 'vendor', 'autoload.php'))) {
    console.error('[vercel-build] vendor/autoload.php still missing after Composer');
    process.exit(1);
  }
}

function runVite() {
  const manifest = path.join(publicDir, 'build', 'manifest.json');
  if (fs.existsSync(manifest)) {
    log('Using committed Vite build assets (public/build)');
    copyDir(publicDir, distDir);
    return;
  }

  log('No committed Vite assets; skipping frontend build on Vercel');
  copyDir(publicDir, distDir);
}

try {
  preparePhpBridge();
  prepareDist();
  runComposer();
  runVite();
  log('Vercel build complete');
} catch (error) {
  console.error('[vercel-build] Fatal error:', error);
  process.exit(1);
}
