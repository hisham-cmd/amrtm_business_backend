<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractClause extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_contract_clauses';

    protected $fillable = [
        'contract_type_id',
        'name',
        'description',
        'sort_order',
    ];

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }
}
