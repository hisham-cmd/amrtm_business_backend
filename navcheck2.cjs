const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push(String(e)));
    page.on('console', (m) => { if (m.type() === 'error' && !m.text().includes('favicon')) errors.push(m.text()); });

    const email = 'nav_' + Date.now() + '@t.local';
    await context.request.post('http://127.0.0.1:8000/api/v1/auth/register', {
        data: { name: 'مستخدم الناف', email, phone: '0551112222', password: 'Passw0rd!X', password_confirmation: 'Passw0rd!X', account_type: 'individual' },
    });
    console.log('0) REGISTER: ok');

    // 1) دخول ناجح عبر النموذج
    await page.goto('http://127.0.0.1:8001/login', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(600);
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'Passw0rd!X');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2500);
    console.log('1) بعد دخول: ' + page.url());

    // 2) الوصول للوحة والتحقق من الناف بار + السايدبار
    await page.goto('http://127.0.0.1:8001/dashboard', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(1800);
    await page.screenshot({ path: 'C:/Users/hisha/AppData/Local/Temp/opencode/shots/dash-with-sidebar.png' });

    const dash = await page.evaluate(() => {
        const sidebar = document.getElementById('dash-sidebar');
        const navAuth = document.getElementById('nb-auth');
        const navGuest = document.getElementById('nb-guest');
        return {
            url: location.pathname,
            sidebarVisible: sidebar ? sidebar.getBoundingClientRect().width > 5 : false,
            sidebarWidth: sidebar ? Math.round(sidebar.getBoundingClientRect().width) : 0,
            sidebarItems: document.querySelectorAll('.sb-item').length,
            navAuthVisible: navAuth ? getComputedStyle(navAuth).display !== 'none' : false,
            navGuestHidden: navGuest ? getComputedStyle(navGuest).display === 'none' : false,
            hasUserName: document.body.innerText.includes('مستخدم الناف'),
            hasGreeting: document.body.innerText.includes('مرحباً'),
            hasBalance: document.body.innerText.includes('رصيدي'),
            body: document.body.innerText.slice(0, 120).replace(/\n/g, ' | '),
        };
    });
    console.log('2) DASHBOARD: ' + JSON.stringify(dash));

    // 3) الصفحة الرئيسية — الناف بار يجب أن يعرض حالة المستخدم
    await page.goto('http://127.0.0.1:8001/', { waitUntil: 'networkidle', timeout: 25000 });
    await page.waitForTimeout(1500);
    const home = await page.evaluate(() => {
        const navAuth = document.getElementById('nb-auth');
        const navGuest = document.getElementById('nb-guest');
        return {
            navAuthVisible: navAuth ? getComputedStyle(navAuth).display !== 'none' : false,
            navGuestHidden: navGuest ? getComputedStyle(navGuest).display === 'none' : false,
            hasLogoutBtn: !!document.getElementById('nb-logout-form'),
        };
    });
    console.log('3) HOME navbar (بعد دخول): ' + JSON.stringify(home));

    console.log('JS ERRORS: ' + errors.length);
    if (errors.length) console.log(errors.slice(0, 3).join('\n---\n'));
    await browser.close();
})();