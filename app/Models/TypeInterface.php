<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * يمثّل حالة تفعيل واجهة معينة لنوع حساب معين.
 * غياب السجل = القيمة الافتراضية للسجل تُؤخذ من DashboardRegistry::defaults().
 */
class TypeInterface extends Model
{
    protected $connection = 'business';

    protected $table = 'bs_type_interfaces';

    protected $fillable = [
        'type_key',
        'interface_key',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}
