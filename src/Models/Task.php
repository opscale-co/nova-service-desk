<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Repositories\TaskRepository;
use Opscale\Validations\Validatable;

class Task extends Model
{
    use HasUlids, SoftDeletes, TaskRepository, Validatable;

    protected $table = 'service_desk_tasks';

    protected $fillable = [
        'request_id',
        'assignee_id',
        'assigner_id',
        'workflow_id',
        'workflow_stage_id',
        'key',
        'title',
        'description',
        'status',
        'status_alias',
        'priority',
        'priority_score',
        'due_date',
        'contacted_at',
        'closed_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => SLAPriority::class,
        'priority_score' => 'float',
        'due_date' => 'datetime',
        'contacted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'assignee_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'assigner_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function workflowStage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }
}
