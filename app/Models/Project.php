<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany,BelongsTo};

class Project extends Model
{
    protected $fillable = [
        'name', 'project_type_id', 'description', 'location',
        'fixed_amount', 'start_date', 'expected_end_date'
    ];

    protected $casts = [
        'fixed_amount'        => 'decimal:2',
        'start_date'          => 'date',
        'expected_end_date'   => 'date',
        'progress_percent'    => 'integer',
    ];

    /* ---------------- Relations ---------------- */

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // (Optional legacy filter you had; safe to keep)
    public function expensesOnly(): HasMany
    {
        return $this->hasMany(Expense::class)
            ->whereHas('accountCode', fn($q) => $q->where('code', '!=', 'AC-40001'));
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProjectStep::class)->orderBy('step_no');
    }

    /* ---------------- Aggregates & Accessors ---------------- */

    /**
     * Total expense amount (uses withSum shortcut if loaded)
     */
    public function getTotalExpenseAttribute(): float
    {
        // If controller used ->withSum('expenses','amount'), Laravel exposes expenses_sum_amount
        if (array_key_exists('expenses_sum_amount', $this->attributes)) {
            return (float) $this->attributes['expenses_sum_amount'];
        }
        return (float) $this->expenses()->sum('amount');
    }

    /**
     * Total income amount (uses withSum shortcut if loaded)
     */
    public function getTotalIncomeAttribute(): float
    {
        if (array_key_exists('incomes_sum_amount', $this->attributes)) {
            return (float) $this->attributes['incomes_sum_amount'];
        }
        return (float) $this->incomes()->sum('amount');
    }

    /**
     * Profit = Income - Expense
     */
    public function getProfitAttribute(): float
    {
        return $this->total_income - $this->total_expense;
    }

    /**
     * Keep legacy name but make it equal to real expenses (no AC-40001 hack).
     * Safe for existing blades that reference $project->final_expense.
     */
    public function getFinalExpenseAttribute(): float
    {
        return $this->total_expense;
    }

    /**
     * % of fixed budget used by expenses (clamped 0–100)
     */
    public function getBudgetUsedPercentAttribute(): int
    {
        $fixed = (float) $this->fixed_amount;
        if ($fixed <= 0) return 0;

        $pct = ($this->total_expense / $fixed) * 100;
        return (int) min(100, max(0, round($pct)));
    }

    /**
     * Steps completion %
     */
    public function getProgressPercentAttribute(): int
    {
        $total = $this->steps()->count();
        if ($total === 0) return 0;
        $done = $this->steps()->where('is_done', true)->count();
        return (int) round(($done / $total) * 100);
    }
}