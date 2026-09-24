<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractType extends Model
{
    protected $connection = 'business';
    protected $table      = 'bs_contract_types';

    protected $fillable = [
        'name',
        'price',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function clauses(): HasMany
    {
        return $this->hasMany(ContractClause::class, 'contract_type_id');
    }
}
