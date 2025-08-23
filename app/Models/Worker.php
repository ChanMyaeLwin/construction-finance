<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    protected $fillable = ['name','phone','role','basic_salary','is_active'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
