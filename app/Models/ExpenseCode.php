<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCode extends Model
{
    protected $fillable = ['code', 'name', 'description'];
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
}