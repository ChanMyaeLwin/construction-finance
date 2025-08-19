<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStep extends Model
{
    protected $fillable = ['project_id','step_no','name','is_done'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}