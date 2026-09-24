const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    const errors = [];
    page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
    page.on('pageerror', (e) => errors.push(String(e)));

    const email = 'test_' + Date.now() + '@test.local';
    const results = {};

    // 1) فتح التسجيل
    await page.goto('http://localhost:5173/register', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(600);
    results['register page'] = (await page.locator('body').innerText()).includes('إنشاء حساب جديد');

    // 2) تعبئة النموذج (نموذج العميل عبر provider-account?mode=client)
    await page.goto('http://localhost:5173/provider-account?mode=client', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(600);
    results['client mode'] = (await page.locator('body').innerText()).includes('إنشاء حساب عميل');

    await page.fill('input[name="name_ar"]', 'مستخدم اختبار');
    await page.fill('input[name="phone"]', '0551234567');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'Passw0rd!Test');
    await page.fill('input[name="password_confirmation"]', 'Passw0rd!Test');
    results['form filled'] = true;

    // 3) إرسال
    await page.click('button[type="submit"]');
    await page.waitForTimeout(3000);
    const bodyAfter = await page.locator('body').innerText();
    results['success screen'] = bodyAfter.includes('تم إرسال الطلب بنجاح') || bodyAfter.includes('أهلاً');

    // 4) التحقق من التوكن المخزن والدخول
    const token = await page.evaluate(() => localStorage.getItem('amrtm_token'));
    results['token stored'] = Boolean(token && token.length > 10);
    const user = await page.evaluate(() => JSON.parse(localStorage.getItem('amrtm_user') || 'null'));
    results['user stored'] = Boolean(user && user.email === email);

    // 5) لوحة المستخدم بعد الدخول
    await page.goto('http://localhost:5173/dashboard', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(1000);
    const dashBody = await page.locator('body').innerText();
    results['dashboard greeting'] = dashBody.includes('أهلاً') || dashBody.includes('مستخدم');

    // 6) استدعاء API مباشرة بالتوكن
    const me = await page.evaluate(async (tok) => {
        const res = await fetch('/api/v1/auth/me', { headers: { 'Authorization': 'Bearer ' + tok } });
        return await res.json();
    }, token);
    results['auth/me works'] = me.isSuccess === true && me.value.user.email === email;

    console.log(JSON.stringify(results, null, 2));
    console.log('JS ERRORS: ' + errors.length);
    if (errors.length) console.log(errors.slice(0, 5).join('\n---\n'));
    await browser.close();
})();