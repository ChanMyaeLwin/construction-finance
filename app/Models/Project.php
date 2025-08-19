<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany,BelongsTo};
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    protected $fillable = [
        'name', 'project_type_id', 'description', 'location',
        'fixed_amount', 'start_date', 'expected_end_date'
    ];

    protected $casts = [
        'fixed_amount' => 'decimal:2',
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'progress_percent' => 'integer',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
    
    public function expensesOnly(): HasMany
    {
        return $this->hasMany(Expense::class)
            ->whereHas('accountCode', fn($q) => $q->where('code', '!=', 'AC-40001'));
    }
    // Accessors
    // public function getTotalExpenseAttribute(): string
    // {
    //     return (string) $this->expenses()->sum('amount');
    // }

    public function getFinalExpenseAttribute(): float
{
    // Signed sum of this project's lines (AC-40001 subtracts)
    $sum = DB::table('expenses')
        ->join('account_codes', 'account_codes.id', '=', 'expenses.account_code_id')
        ->where('expenses.project_id', $this->id)
        ->selectRaw("COALESCE(SUM(CASE WHEN account_codes.code = 'AC-40001'
            THEN -expenses.amount ELSE expenses.amount END), 0) as s")
        ->value('s');

    return (float) $sum;
}

    public function getProfitAttribute(): string
    {
        return (string) ((float)$this->fixed_amount - (float)$this->final_expense);
    }

    public function getBudgetUsedPercentAttribute(): int
    {
        $fixed = (float) $this->fixed_amount;
        if ($fixed <= 0) return 0;
        $pct = ((float)$this->final_expense / $fixed) * 100;
        return (int) min(100, round($pct));
    }

    public function projectType(): BelongsTo {
        return $this->belongsTo(ProjectType::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProjectStep::class)->orderBy('step_no');
    }

    public function getProgressPercentAttribute(): int
    {
        $total = $this->steps()->count();
        if ($total === 0) return 0;
        $done = $this->steps()->where('is_done', true)->count();
        return (int) round(($done / $total) * 100);
    }
}