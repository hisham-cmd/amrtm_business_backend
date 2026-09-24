const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    const errors = [];
    page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
    page.on('pageerror', (e) => errors.push(String(e)));

    const routes = [
        '/', '/catalog/ministries', '/catalog/authorities/1', '/offices/law', '/offices/accounting',
        '/law-info', '/accounting-info', '/consultants', '/consultants/1', '/consultants/specialty/1',
        '/login', '/register', '/office/login', '/dashboard', '/dashboard-hub', '/requests/1/track',
        '/contracts/my', '/contracts/incoming', '/contracts/1', '/create-contract', '/payment/checkout',
        '/provider-account/create', '/provider-account?mode=consultant', '/provider-account?mode=client',
        '/nafath', '/nafath/wait',
        '/admin', '/admin/requests', '/admin/offices', '/admin/users', '/admin/finance',
        '/admin/homepage', '/admin/icons', '/admin/messages',
        '/office/dashboard', '/office/profile',
    ];

    let ok = 0;
    const failures = [];
    for (const r of routes) {
        try {
            await page.goto('http://localhost:5173' + r, { waitUntil: 'networkidle', timeout: 25000 });
            await page.waitForTimeout(400);
            ok++;
        } catch (e) {
            failures.push(r + ': ' + String(e).slice(0, 80));
        }
    }

    console.log('PAGES OK: ' + ok + ' / ' + routes.length);
    if (failures.length) console.log('FAILURES:\n' + failures.join('\n'));
    console.log('JS ERRORS: ' + errors.length);
    if (errors.length) console.log(errors.slice(0, 8).join('\n---\n'));

    // اختبار تفاعلي: التبديل بين أوضاع provider-account
    await page.goto('http://localhost:5173/provider-account/create', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(600);
    const body1 = await page.locator('body').innerText();
    console.log('PROVIDER has office mode: ' + body1.includes('تسجيل منشأة جديدة'));
    console.log('PROVIDER has office types: ' + body1.includes('نوع المكتب'));

    await page.goto('http://localhost:5173/provider-account?mode=consultant', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(600);
    const body2 = await page.locator('body').innerText();
    console.log('PROVIDER consultant mode: ' + body2.includes('تسجيل مستشار جديد'));

    await browser.close();
})();