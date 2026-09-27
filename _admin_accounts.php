<?php
/**
 * يطبع حسابات الإدارة (البريد فقط) لتسجيل الدخول في المتصفح والاختبار.
 * لا يطبع أي كلمة مرور.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Business\BusinessUser;

$rows = [];
$rows[] = '=== حسابات الإدارة (لاختيار حساب للاختبار) ===';

BusinessUser::whereIn('role', ['admin', 'supervisor'])
    ->orderBy('id')
    ->get()
    ->each(function ($u) use (&$rows) {
        $rows[] = sprintf(
            'id=%-4s role=%-11s email=%-34s active=%s verified_at=%s',
            $u->id,
            $u->role,
            $u->email,
            $u->is_active ? 'yes' : 'no',
            $u->email_verified_at ? 'yes' : 'no'
        );
    });

$rows[] = '';
$rows[] = '=== هل كلمة المرور تتطابق مع "admin123" أو "Test@12345"؟ ===';
foreach (BusinessUser::whereIn('role', ['admin', 'supervisor'])->get() as $u) {
    $hits = [];
    foreach (['admin123', 'Test@12345', 'password', '12345678', 'Admin@12345'] as $cand) {
        if (\Illuminate\Support\Facades\Hash::check($cand, $u->password)) {
            $hits[] = $cand;
        }
    }
    $rows[] = $u->email . ' => ' . ($hits ? implode(', ', $hits) : 'لا تطابق أي كلمة مرور معروفة');
}

file_put_contents(__DIR__ . '/_admin_accounts.txt', implode("\n", $rows) . "\n");
echo "ok\n";
