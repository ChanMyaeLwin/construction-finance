<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'project_id', 'account_code_id', 'user_id',
        'expense_date', 'amount', 'description','worker_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function accountCode(): BelongsTo { return $this->belongsTo(AccountCode::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function worker()
    {
        return $this->belongsTo(\App\Models\Worker::class);
    }
}