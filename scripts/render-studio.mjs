// Photographs render studio pages with a headless browser.
//
// Usage: node scripts/render-studio.mjs <manifest.json>
// The manifest is a list of { url, path, width, height }. Each URL is opened at that size and,
// once the page reports that its scene is drawn, saved as a PNG to `path`.
//
// The browser is the Chrome at CHROME_PATH if set, else an installed Chrome, else the
// Chromium that `npx playwright install chromium` downloads. The scenes are WebGL, so the
// software renderer is switched on for machines without a GPU.
import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const manifestPath = process.argv[2];
if (!manifestPath) {
    console.error('Usage: node scripts/render-studio.mjs <manifest.json>');
    process.exit(2);
}

const shots = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
const args = ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--hide-scrollbars'];

async function launch() {
    const attempts = process.env.CHROME_PATH
        ? [{ executablePath: process.env.CHROME_PATH }]
        : [{ channel: 'chrome' }, {}];

    let lastError;
    for (const options of attempts) {
        try {
            return await chromium.launch({ headless: true, args, ...options });
        } catch (error) {
            lastError = error;
        }
    }
    throw lastError;
}

let browser;
try {
    browser = await launch();

    for (const shot of shots) {
        const context = await browser.newContext({ viewport: { width: shot.width, height: shot.height }, deviceScaleFactor: 1 });
        const page = await context.newPage();

        try {
            const response = await page.goto(shot.url, { waitUntil: 'load', timeout: 60000 });
            if (!response || !response.ok()) {
                throw new Error(`${shot.url} answered ${response ? response.status() : 'nothing'}`);
            }

            await page.waitForFunction(() => window.__renderReady === true || typeof window.__renderError === 'string', null, { timeout: 60000 });
            const renderError = await page.evaluate(() => window.__renderError ?? null);
            if (renderError) throw new Error(`${shot.url} could not draw its scene: ${renderError}`);

            fs.mkdirSync(path.dirname(shot.path), { recursive: true });
            await page.screenshot({ path: shot.path, type: 'png' });
        } finally {
            await context.close();
        }
    }
} catch (error) {
    console.error(error instanceof Error ? error.message : String(error));
    process.exitCode = 1;
} finally {
    await browser?.close();
}
