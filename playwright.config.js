export default {
    testDir: './tests/e2e',
    timeout: 120_000,
    expect: { timeout: 20_000 },
    fullyParallel: false,
    workers: 1,
    retries: 1,
    reporter: process.env.CI === 'true'
        ? [['list'], ['html', { open: 'never' }]]
        : 'list',
    use: {
        baseURL: process.env.E2E_BASE_URL,
        locale: 'ar-SA',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
};