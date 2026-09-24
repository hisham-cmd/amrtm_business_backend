<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| توسيع ENUM document_type في bs_office_documents
|--------------------------------------------------------------------------
|
| عمود document_type في قاعدة MySQL الفعلية هو ENUM بقيم
| ('license','commercial_register','cv','certificate','award','client','experience')
| بينما كود تسجيل المكتب (ProviderAccountController) ولوحة المكتب
| (OfficeProfileController) يُدرجان document_type بقيمتي
| trademark_certificate و appreciation_certificate → خطأ 1265 Data truncated
| على MySQL عند رفع شهادة العلامة التجارية أو شهادات التقدير.
|
| الحل: توسيع الـ ENUM بالقيمتين المفقودتين. الـ migration محمي بـ mysql
| لأن الاختبارات تعمل على SQLite في الذاكرة (لا يعرف عبارة ALTER ... ENUM).
|
*/

return new class extends Migration
{
    protected $connection = 'business';

    private array $documentTypes = [
        'license',
        'commercial_register',
        'cv',
        'certificate',
        'award',
        'client',
        'experience',
    ];

    private array $addedDocumentTypes = [
        'trademark_certificate',
        'appreciation_certificate',
    ];

    public function up(): void
    {
        if (DB::connection($this->connection)->getDriverName() !== 'mysql') {
            return;
        }

        $this->alterEnum(array_merge($this->documentTypes, $this->addedDocumentTypes));
    }

    public function down(): void
    {
        if (DB::connection($this->connection)->getDriverName() !== 'mysql') {
            return;
        }

        $this->alterEnum($this->documentTypes);
    }

    private function alterEnum(array $values): void
    {
        $enum = implode(',', array_map(fn (string $value): string => "'" . $value . "'", $values));

        DB::connection($this->connection)->statement(
            "ALTER TABLE `bs_office_documents` MODIFY `document_type` ENUM({$enum}) NOT NULL"
        );
    }
};