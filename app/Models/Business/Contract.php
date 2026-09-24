<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_contracts';

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_SIGNED   = 'signed';
    public const STATUS_EXPIRED  = 'expired';

    public const STATUS_LABELS = [
        'active'  => 'ساري',
        'pending' => 'بانتظار التوقيع',
        'signed'  => 'موقع',
        'expired' => 'منتهي',
    ];

    public const PARTY2_PENDING   = 'pending';
    public const PARTY2_INVITED   = 'invited';
    public const PARTY2_REGISTERED = 'registered';
    public const PARTY2_SIGNED    = 'signed';

    public const PARTY2_STATUS_LABELS = [
        'pending'    => 'بانتظار الدعوة',
        'invited'    => 'تمت دعوته',
        'registered' => 'مسجّل وتمت المطابقة',
        'signed'     => 'موقّع',
    ];

    protected $fillable = [
        'number',
        'contract_type_id',
        'price',
        'clauses_json',
        'party_name',
        'party_1_office_id',
        'party_1_name',
        'party_2_email',
        'party_2_office_id',
        'party_2_status',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'clauses_json' => 'array',
        'price'        => 'float',
        'start_date'   => 'date',
        'end_date'     => 'date',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }

    public function partyOneOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'party_1_office_id');
    }

    public function partyTwoOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'party_2_office_id');
    }

    public function party2StatusLabel(): string
    {
        return self::PARTY2_STATUS_LABELS[$this->party_2_status] ?? $this->party_2_status;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
