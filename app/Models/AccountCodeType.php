<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountCodeType extends Model
{
    protected $fillable = ['name', 'description'];

    public function accountCodes(): HasMany
    {
        return $this->hasMany(AccountCode::class);
    }
}
