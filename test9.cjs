const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    const errors = [];
    page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
    page.on('pageerror', (e) => errors.push(String(e)));

    const email = 'test_' + Date.now() + '@test.local';
    const pass = 'Passw0rd!Test';
    const results = {};

    // 1) التسجيل كعميل
    await page.goto('http://localhost:5173/provider-account?mode=client', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(600);
    await page.fill('input[name="name_ar"]', 'مستخدم اختبار نهائي');
    await page.fill('input[name="phone"]', '0551234567');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', pass);
    await page.fill('input[name="password_confirmation"]', pass);
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2500);
    results['1-register-success'] = (await page.locator('body').innerText()).includes('تم إرسال الطلب بنجاح');

    // 2) تسجيل الخروج (مسح التوكن)
    await page.evaluate(() => localStorage.clear());

    // 3) الدخول من صفحة /login
    await page.goto('http://localhost:5173/login', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(600);
    await page.fill('input[type="email"]', email);
    await page.fill('input[type="password"]', pass);
    await page.click('button[type="submit"]');
    await page.waitForTimeout(3000);
    results['2-login-redirect'] = page.url().includes('/dashboard');

    // 4) لوحة التحكم تعرض البيانات
    const dashBody = await page.locator('body').innerText();
    results['3-dashboard-greeting'] = dashBody.includes('أهلاً');
    results['4-dashboard-stats'] = dashBody.includes('طلبات') || dashBody.includes('طلب');

    // 5) API بحماية التوكن يعمل
    const token = await page.evaluate(() => localStorage.getItem('amrtm_token'));
    const me = await page.evaluate(async (tok) => {
        const res = await fetch('/api/v1/auth/me', { headers: { 'Authorization': 'Bearer ' + tok } });
        return res.status + ':' + JSON.stringify(await res.json()).slice(0, 120);
    }, token);
    results['5-auth-me'] = me.startsWith('200');

    // 6) مسارات المحمي ترفض بدون توكن (بعد مسحه)
    await page.evaluate(() => localStorage.clear());
    const pmin = await page.evaluate(async () => {
        const res = await fetch('/api/v1/requests', { headers: { 'Accept': 'application/json' } });
        return res.status;
    });
    results['6-protected-401'] = pmin === 401;

    console.log(JSON.stringify(results, null, 2));
    console.log('JS ERRORS: ' + errors.length);
    if (errors.length) console.log(errors.slice(0, 4).join('\n---\n'));
    await browser.close();
})();