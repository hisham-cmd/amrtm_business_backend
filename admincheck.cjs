const { chromium } = require('playwright');
const fs = require('fs');

const TOKEN = '23|1pal1eONAxDGl0B8Bjk906Mx1VCUdtnNz5W5dRZ4e1d39f93';
const NEW = 'http://localhost:5173';
const OLD = 'http://127.0.0.1:8000';
const SHOT = 'C:\\Users\\hisha\\AppData\\Local\\Temp\\opencode\\shots\\admin';
if (!fs.existsSync(SHOT)) fs.mkdirSync(SHOT, { recursive: true });

(async () => {
    const browser = await chromium.launch();
    const report = [];

    // ═══ 1) فتح لوحة الأدمن الجديدة (React) بالتوكين ═══
    const newPage = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    const jsErrors = [];
    newPage.on('pageerror', (e) => jsErrors.push(String(e)));
    newPage.on('console', (m) => { if (m.type() === 'error') jsErrors.push(m.text()); });

    // حقن التوكن في localStorage قبل التحميل
    await newPage.addInitScript((tok) => {
        localStorage.setItem('amrtm_token', tok);
        localStorage.setItem('amrtm_user', JSON.stringify({ id: 54, name: 'مدير الاختبار', email: 'admin@amrtm.dev', role: 'admin', type: 'business', is_admin: true }));
    }, TOKEN);

    await newPage.goto(NEW + '/admin', { waitUntil: 'networkidle', timeout: 30000 });
    await newPage.waitForTimeout(3000);
    await newPage.screenshot({ path: SHOT + '\\01-new-admin-overview.png', fullPage: true });

    const adminOverview = await newPage.evaluate(() => ({
        url: location.pathname,
        hasSidebar: !!document.querySelector('#dash-sidebar'),
        hasTopbar: !!document.querySelector('header'),
        title: document.querySelector('#dash-page-title')?.textContent?.trim() || document.querySelector('h1')?.textContent?.trim(),
        hasStatsCards: document.querySelectorAll('.rounded-2xl').length,
        hasRequestsTable: !!Array.from(document.querySelectorAll('table')).find((t) => t.textContent.includes('الطلبات')),
        hasMenu: !!document.querySelector('.sidebar-item'),
        menuItems: Array.from(document.querySelectorAll('.sidebar-item')).map((el) => el.textContent.trim().slice(0, 40)),
        bodySnippet: document.body.innerText.slice(0, 250).replace(/\n/g, ' | '),
    }));
    report.push({ route: 'NEW /admin (overview)', data: adminOverview, jsErrors: jsErrors.length });
    console.log('═══ NEW ADMIN OVERVIEW ═══');
    console.log(JSON.stringify(adminOverview, null, 2));

    // ═══ 2) تبويبات الأدمن الجديدة ═══
    for (const [name, path] of Object.entries({
        requests: '/admin/requests',
        offices: '/admin/offices',
        users: '/admin/users',
        finance: '/admin/finance',
        homepage: '/admin/homepage',
    })) {
        await newPage.goto(NEW + path, { waitUntil: 'networkidle', timeout: 30000 });
        await newPage.waitForTimeout(2500);
        const info = await newPage.evaluate(() => ({
            url: location.pathname,
            title: document.querySelector('#dash-page-title')?.textContent?.trim(),
            hasTable: document.querySelectorAll('table').length > 0,
            hasContent: document.body.innerText.length > 300,
            snippet: document.body.innerText.slice(0, 150).replace(/\n/g, ' | '),
        }));
        report.push({ route: 'NEW ' + path, data: info, jsErrors: jsErrors.length });
        console.log('═══ NEW ' + path + ' ═══');
        console.log(JSON.stringify(info, null, 2));
    }

    // ═══ 3) لوحة الأدمن القديمة (Blade) ═══
    const oldPage = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    // الواجهة القديمة تستخدم session cookies — نفتح صفحة الأدمن القديمة ونتحقق من التوجيه
    try {
        const resp = await oldPage.goto(OLD + '/admin', { waitUntil: 'domcontentloaded', timeout: 20000 });
        await oldPage.waitForTimeout(1500);
        const oldInfo = await oldPage.evaluate(() => ({
            url: location.pathname,
            status: document.title,
            hasSidebar: !!document.querySelector('#dash-sidebar, .sidebar, [class*="sidebar"]'),
            bodySnippet: document.body.innerText.slice(0, 200).replace(/\n/g, ' | '),
        }));
        report.push({ route: 'OLD /admin', data: oldInfo });
        await oldPage.screenshot({ path: SHOT + '\\05-old-admin-anon.png' });
        console.log('═══ OLD ADMIN ═══');
        console.log(JSON.stringify(oldInfo, null, 2));
    } catch (e) {
        report.push({ route: 'OLD /admin', error: String(e).slice(0, 80) });
    }

    // ═══ 4) مقارنة بنية اللوحة: القوائم ═══
    await newPage.goto(NEW + '/admin', { waitUntil: 'networkidle', timeout: 30000 });
    await newPage.waitForTimeout(2500);
    const structure = await newPage.evaluate(() => {
        const sidebar = document.querySelector('#dash-sidebar');
        return {
            sidebarWidth: sidebar?.getBoundingClientRect().width,
            sidebarHeight: sidebar?.getBoundingClientRect().height,
            hasDarkModeBtn: !!Array.from(document.querySelectorAll('button')).find((b) => b.querySelector('.ti-moon, .ti-sun')),
            hasNotifBtn: !!Array.from(document.querySelectorAll('button')).find((b) => b.querySelector('.ti-bell')),
            hasUserMenu: !!document.querySelector('[class*="user"]'),
            contentPadding: document.querySelector('main') ? getComputedStyle(document.querySelector('main')).padding : null,
        };
    });
    report.push({ route: 'NEW structure', data: structure });
    console.log('═══ NEW STRUCTURE ═══');
    console.log(JSON.stringify(structure, null, 2));

    // ═══ 5) أزرار التفاعل: الوضع الليلي والإشعارات ═══
    await newPage.click('button[aria-label="تبديل المظهر"]').catch(async () => {
        // محاولة بديلة
        await newPage.evaluate(() => {
            const btn = Array.from(document.querySelectorAll('button')).find((b) => b.querySelector('.ti-moon, .ti-sun'));
            if (btn) btn.click();
        });
    });
    await newPage.waitForTimeout(800);
    const darkMode = await newPage.evaluate(() => ({
        htmlDark: document.documentElement.classList.contains('dark'),
    }));
    report.push({ route: 'NEW darkmode', data: darkMode });
    console.log('═══ DARK MODE ═══: ' + JSON.stringify(darkMode));

    // ═══ 6) إشعارات ═══
    await newPage.evaluate(() => {
        const btn = Array.from(document.querySelectorAll('button')).find((b) => b.querySelector('.ti-bell'));
        if (btn) btn.click();
    });
    await newPage.waitForTimeout(1000);
    const notif = await newPage.evaluate(() => ({
        dropdownVisible: document.body.innerText.includes('الإشعارات'),
        hasUnreadBadge: !!document.querySelector('[class*="red-500"]'),
    }));
    report.push({ route: 'NEW notifications', data: notif });
    console.log('═══ NOTIFICATIONS ═══: ' + JSON.stringify(notif));

    await newPage.screenshot({ path: SHOT + '\\06-new-admin-final.png', fullPage: true });

    // ═══ 7) التحقق من مطابقة السايدبار القديم ═══
    // نقرأ الـ DashboardRegistry القديم لمعرفة عناصر القائمة المتوقعة
    try {
        const fsmod = require('fs');
        const registryFile = 'C:\\react_projects\\amrtm_business\\amrtm_business_backend\\app\\Support\\DashboardRegistry.php';
        if (fsmod.existsSync(registryFile)) {
            const content = fsmod.readFileSync(registryFile, 'utf8');
            const arLabels = content.match(/'ar'\s*=>\s*'([^']+)'/g)?.slice(0, 20) || [];
            report.push({ route: 'OLD registry labels', data: arLabels.map((l) => l.replace(/'ar'\s*=>\s*'/, '').replace(/'$/, '')) });
            console.log('═══ OLD REGISTRY (عينة) ═══');
            console.log(JSON.stringify(arLabels.slice(0, 15).map((l) => l.replace(/'ar'\s*=>\s*'/, '').replace(/'$/, '')), null, 2));
        }
    } catch (e) { /* ignore */ }

    console.log('\n═══ JS ERRORS (new) ═══: ' + jsErrors.length);
    const unique = [...new Set(jsErrors.filter((e) => !e.includes('401')).map((e) => e.slice(0, 90)))];
    if (unique.length) console.log(unique.slice(0, 5).join('\n---\n'));
    console.log('Screenshots: ' + SHOT);

    await browser.close();
})();