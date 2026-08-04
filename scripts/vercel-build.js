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

function chmodSafe(filePath) {
  try {
    fs.chmodSync(filePath, 0o755);
  } catch (_) {}
}

function copyDir(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  for (const entry of fs.readdirSync(src, { withFileTypes: true })) {
    const from = path.join(src, entry.name);
    const to = path.join(dest, entry.name);
    if (entry.isDirectory()) {
      copyDir(from, to);
    } else if (entry.isFile()) {
      fs.copyFileSync(from, to);
    }
  }
}

function preparePhpBridge() {
  if (!fs.existsSync(srcPhpRoot)) {
    console.error('Missing @libphp package. Run npm install first.');
    process.exit(1);
  }

  console.log('Copying PHP runtime into api/_phpbridge...');
  fs.rmSync(bridgeRoot, { recursive: true, force: true });
  copyDir(path.join(srcPhpRoot, 'php'), phpDir);
  copyDir(path.join(srcPhpRoot, 'lib'), libDir);
  chmodSafe(phpBin);
  chmodSafe(path.join(phpDir, 'php-cgi'));
  chmodSafe(composerBin);
}

function prepareDist() {
  console.log('Preparing dist static assets...');
  fs.rmSync(distDir, { recursive: true, force: true });
  fs.mkdirSync(distDir, { recursive: true });
  if (fs.existsSync(publicDir)) {
    copyDir(publicDir, distDir);
  }
  fs.writeFileSync(path.join(distDir, '.vercel-static'), 'ok\n');
}

function runComposer() {
  console.log('Installing Composer dependencies with bundled PHP...');
  if (process.platform !== 'linux') {
    console.log(`Skipping Composer on platform=${process.platform}`);
    if (!fs.existsSync(path.join(root, 'vendor', 'autoload.php'))) {
      console.warn('WARNING: vendor/autoload.php missing.');
    }
    return;
  }

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
      '--no-progress',
    ],
    {
      cwd: root,
      stdio: 'inherit',
      env: {
        ...process.env,
        PATH: `${phpDir}:${process.env.PATH || ''}`,
        LD_LIBRARY_PATH: `${libDir}:${process.env.LD_LIBRARY_PATH || ''}`,
      },
    }
  );

  if (result.status !== 0) {
    console.error('Composer install failed');
    process.exit(result.status || 1);
  }
}

function runVite() {
  const manifest = path.join(publicDir, 'build', 'manifest.json');
  if (fs.existsSync(manifest)) {
    console.log('Using committed Vite build assets (public/build)');
    if (fs.existsSync(publicDir)) {
      copyDir(publicDir, distDir);
    }
    return;
  }

  console.log('Building frontend assets...');
  const npmBuild = spawnSync('npm', ['run', 'build', '--if-present'], {
    cwd: root,
    stdio: 'inherit',
    shell: process.platform === 'win32',
  });
  if (npmBuild.status !== 0) {
    console.error('npm run build failed');
    process.exit(npmBuild.status || 1);
  }
  if (fs.existsSync(publicDir)) {
    copyDir(publicDir, distDir);
  }
}

preparePhpBridge();
prepareDist();
runComposer();
runVite();
console.log('Vercel build complete');
