<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\Validations\Validatable;

class WorkflowStage extends Model
{
    use HasUlids, Validatable;

    public static array $validationRules = [
        'workflow_id' => 'required',
        'name' => 'required|string|max:256',
        'maps_to_status' => 'required|string',
    ];

    protected $table = 'service_desk_workflow_stages';

    protected $fillable = [
        'workflow_id',
        'name',
        'description',
        'color',
        'maps_to_status',
    ];

    protected $casts = [
        'maps_to_status' => TaskStatus::class,
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'workflow_stage_id');
    }
}
