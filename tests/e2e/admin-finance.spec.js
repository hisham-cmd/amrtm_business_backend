import { test, expect } from 'playwright/test';

const BASE_URL = process.env.E2E_BASE_URL;
const ADMIN_EMAIL = process.env.E2E_ADMIN_EMAIL || 'admin@amrtm.dev';
const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD || 'secret123';
const CLIENT_SEARCH = process.env.E2E_CLIENT_SEARCH || 'client@amrtm.dev';

test.describe('لوحة الأدمن — الحركة المالية وشحن الرصيد اليدوي', () => {
    test.beforeEach(async ({}, testInfo) => {
        test.skip(!BASE_URL, 'E2E_BASE_URL غير مضبوطة — تم تخطي الاختبار. ');
        test.skip(
            !ADMIN_EMAIL || !ADMIN_PASSWORD,
            'E2E_ADMIN_EMAIL / E2E_ADMIN_PASSWORD غير مضبوطة — تم تخطي الاختبار. '
        );
    });

    test('فتح صفحة المالية + شحن رصيد عميل يدوياً + ظهوره في السجل', async ({ page }) => {
        await page.goto('/login');

        await page.fill('#login-email', ADMIN_EMAIL);
        await page.fill('#login-password', ADMIN_PASSWORD);
        await page.click('#login-section form button[type="submit"]');

        await page.waitForURL(/\/(dashboard-hub)(\/|\?|$)/, { timeout: 30_000 });

        await page.goto(BASE_URL + '/admin#finance');
        await page.waitForLoadState('networkidle');

        // Summaries + ledger should render (table may legitimately be empty)
        await expect(page.locator('#fin-total')).toBeVisible({ timeout: 15_000 });
        await expect(page.locator('#fin-tbody')).toBeVisible();
        await expect(page.locator('#fin-pager')).toBeVisible();

        // Open the manual charge (Flowbite) modal
        await page.click('button[onclick="openManualCharge()"]');
        await expect(page.locator('#manual-charge-modal')).toBeVisible({
            timeout: 10_000,
        });

        // Search a client in the combobox and pick it (typing triggers debounced search)
        await page.fill('#mc-search', CLIENT_SEARCH);
        const firstResult = page.locator('#mc-results button[onclick^="pickMchargeClient"]').first();
        await expect(firstResult).toBeVisible({ timeout: 10_000 });
        await firstResult.click();
        await expect(page.locator('#mc-clear-btn')).toBeVisible();

        // Charge a small amount and submit
        await page.fill('#mc-amount', '10');
        await page.click('#mc-submit-btn');

        await expect(page.locator('#manual-charge-modal')).toBeHidden({
            timeout: 15_000,
        });
        await page.locator('#toast').waitFor({ state: 'visible', timeout: 5_000 }).catch(() => null);

        // The ledger refreshes and the new charge appears at the top of the list
        const firstRow = page.locator('#fin-tbody tr').first();
        await expect(firstRow).toContainText(CLIENT_SEARCH, { timeout: 15_000 });
        await expect(firstRow).toContainText('شحن رصيد');
    });
});