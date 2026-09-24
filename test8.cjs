const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    page.on('console', (m) => { if (m.type() === 'error') console.log('CONSOLE ERR:', m.text()); });
    page.on('pageerror', (e) => console.log('PAGE ERR:', String(e)));

    const email = 'test_' + Date.now() + '@test.local';

    await page.goto('http://localhost:5173/provider-account?mode=client', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(800);

    await page.fill('input[name="name_ar"]', 'مستخدم اختبار');
    await page.fill('input[name="phone"]', '0551234567');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'Passw0rd!Test');
    await page.fill('input[name="password_confirmation"]', 'Passw0rd!Test');

    // التقاط طلب الشبكة
    let lastResponse = null;
    page.on('response', (res) => {
        if (res.url().includes('/api/v1/auth/register')) {
            res.text().then((t) => {
                lastResponse = { status: res.status(), body: t.slice(0, 400) };
                console.log('REGISTER RESPONSE:', JSON.stringify(lastResponse));
            }).catch(() => {});
        }
    });

    await page.click('button[type="submit"]');
    await page.waitForTimeout(4000);

    const body = await page.locator('body').innerText();
    console.log('BODY SNIPPET:', body.slice(0, 300).replace(/\n/g, ' | '));
    const token = await page.evaluate(() => localStorage.getItem('amrtm_token'));
    console.log('TOKEN stored:', Boolean(token));

    await browser.close();
})();