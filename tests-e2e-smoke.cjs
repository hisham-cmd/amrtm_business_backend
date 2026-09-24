const { chromium } = require('playwright');

const BASE = 'http://127.0.0.1:8014';
const PAGES = {
  home: '/',
  login: '/login',
  catalog: '/catalog/ministries',
};

(async () => {
  const browser = await chromium.launch();
  const results = [];
  let failed = 0;

  for (const [name, path] of Object.entries(PAGES)) {
    const page = await browser.newPage({ locale: 'ar-SA' });
    const errors = [];
    page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
    page.on('pageerror', (e) => errors.push('pageerror: ' + e.message));

    try {
      const resp = await page.goto(BASE + path, { waitUntil: 'networkidle', timeout: 30000 });
      const status = resp ? resp.status() : 'none';
      const title = await page.title();
      const hasEntries = await page.locator('body').count();

      let navOk = true;
      // Navbar (partials/public or amrtm navbar) presence heuristic:
      try { await page.waitForSelector('nav', { timeout: 5000 }); } catch { navOk = false; }

      results.push({
        name, status, title,
        navPresent: navOk,
        consoleErrors: errors,
      });

      if (status !== 200 || errors.length) failed++;
    } catch (e) {
      results.push({ name, status: 'ERR', title: '-', navPresent: false, consoleErrors: [e.message] });
      failed++;
    }
    await page.close();
  }

  await browser.close();

  console.log(JSON.stringify(results, null, 2));
  console.log(`\nFAILED: ${failed}/${results.length}`);
  process.exit(failed ? 1 : 0);
})();