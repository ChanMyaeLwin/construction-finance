<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'account_code_id',
        'user_id',
        'income_date',
        'amount',
        'description',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function accountCode()
    {
        return $this->belongsTo(AccountCode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Guardrail: Only Revenue accounts allowed
     */
    protected static function booted()
    {
        static::creating(function ($income) {
            if ($income->accountCode && $income->accountCode->type->name !== 'Revenue') {
                throw new \Exception('Only Revenue accounts can be stored in Incomes.');
            }
        });

        static::updating(function ($income) {
            if ($income->accountCode && $income->accountCode->type->name !== 'Revenue') {
                throw new \Exception('Only Revenue accounts can be stored in Incomes.');
            }
        });
    }
}