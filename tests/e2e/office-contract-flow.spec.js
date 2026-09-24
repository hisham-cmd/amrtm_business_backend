import { test, expect } from 'playwright/test';

const BASE_URL = process.env.E2E_BASE_URL;
const OFFICE_EMAIL = process.env.E2E_OFFICE_EMAIL;
const OFFICE_PASSWORD = process.env.E2E_OFFICE_PASSWORD;

test.describe('لوحة المكتب — تدفق إنشاء عقد جديد', () => {
    test.beforeEach(async ({}, testInfo) => {
        test.skip(!BASE_URL, 'E2E_BASE_URL غير مضبوطة — تم تخطي الاختبار. ');
        test.skip(
            !OFFICE_EMAIL || !OFFICE_PASSWORD,
            'E2E_OFFICE_EMAIL / E2E_OFFICE_PASSWORD غير مضبوطة — تم تخطي الاختبار. '
        );
    });

    test('تسجيل دخول المكتب واختيار نوع العقد وإنشاء عقد جديد', async ({ page }) => {
        await page.goto('/login?type=office');

        await page.fill('#login-email', OFFICE_EMAIL);
        await page.fill('#login-password', OFFICE_PASSWORD);
        await page.click('#login-section form button[type="submit"]');

        await page.waitForURL(/\/(dashboard-hub|office\/complete)(\/|\?|$)/, {
            timeout: 30_000,
        });

        const officeLink = page.locator('a[href*="/office/dashboard"]').first();
        if (!(await officeLink.count())) {
            test.skip(true, 'حساب المكتب لم يكمل ملفه بعد — تم تخطي الاختبار.');
        }
        await officeLink.click();
        await page.waitForURL(/\/office\/dashboard(\/|#|\?|$)/, { timeout: 30_000 });

        // Navigate to the contracts section (the hub link lands on #reqs)
        await page.evaluate(() => { window.location.hash = 'contracts'; });
        await expect(page.locator('#cm-body')).toBeVisible({ timeout: 15_000 });
        await expect(page.locator('#cm-total')).toBeVisible();

        await page.click('button[onclick="openCreateContractModal()"]');
        await expect(page.locator('#cm-create-modal')).toBeVisible({
            timeout: 15_000,
        });

        await page.locator('#cm-step-1').waitFor({ state: 'visible' });
        const firstType = page.locator('#cm-type-grid .cm-tpl-card').first();
        if (!(await firstType.count())) {
            test.skip(true, 'لا توجد أنواع عقود مسجلة في النظام — تم تخطي الاختبار.');
        }

        await firstType.waitFor({ state: 'visible', timeout: 20_000 });
        await firstType.click();
        await expect(page.locator('#cm-next-btn')).toBeEnabled({ timeout: 10_000 });

        await page.click('#cm-next-btn');
        await expect(page.locator('#cm-step-2')).toBeVisible();
        await expect(page.locator('#cm-submit-btn')).toBeVisible();
        await expect(page.locator('#cm-selected-type')).not.toBeEmpty({
            timeout: 5_000,
        });

        await page.fill('#cm-party2-input', 'عميل اختبار E2E');
        await page.fill('#cm-party2-email', 'e2e-client@example.com');
        await page.fill('#cm-start-input', '2026-09-10');
        await page.fill('#cm-end-input', '2027-09-10');
        await page.fill('#cm-desc-input', 'عقد يتم إنشاؤه آلياً من تدفق اختبار E2E');

        // Move focus outside the datepickers so their dropdowns close (Escape would close the whole modal)
        await page.locator('#cm-selected-type').click();

        await page.click('#cm-add-clause-btn');
        await page.fill('#cm-new-clause-name', 'بند مراجعة دورية');
        await page.fill('#cm-new-clause-desc', 'مراجعة العقد كل ستة أشهر');
        await page.click('button[onclick="confirmAddClause()"]');
        await expect(page.locator('#cm-custom-clauses')).toContainText(
            'بند مراجعة دورية'
        );

        await page.click('#cm-submit-btn');
        await expect(page.locator('#cm-create-modal')).toBeHidden({
            timeout: 15_000,
        });
        await page.locator('#toast').waitFor({ state: 'visible', timeout: 5_000 }).catch(() => null);

        await expect(page.locator('#cm-body')).toContainText('عميل اختبار E2E', {
            timeout: 20_000,
        });
    });
});