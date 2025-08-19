<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany,BelongsTo};

class AccountCode extends Model
{
    
    protected $fillable = ['account_code_type_id', 'code', 'name', 'description'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(AccountCodeType::class, 'account_code_type_id');
    }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
}