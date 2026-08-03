import { copyFileSync, existsSync, readdirSync } from 'node:fs';
import { join } from 'node:path';

const root = join(import.meta.dirname, '..');
const buildDir = join(root, 'public', 'build');
const publicDir = join(root, 'public');

const swSource = join(buildDir, 'sw.js');
const manifestSource = join(buildDir, 'manifest.webmanifest');

if (!existsSync(swSource)) {
    console.warn('PWA sync skipped: public/build/sw.js not found.');
    process.exit(0);
}

copyFileSync(swSource, join(publicDir, 'sw.js'));
copyFileSync(manifestSource, join(publicDir, 'manifest.webmanifest'));

const workboxFile = readdirSync(buildDir).find(
    (file) => file.startsWith('workbox-') && file.endsWith('.js'),
);

if (workboxFile) {
    copyFileSync(join(buildDir, workboxFile), join(publicDir, workboxFile));
}

console.log('Synced PWA service worker and manifest to public root.');
