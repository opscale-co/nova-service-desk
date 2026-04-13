<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Opscale\NovaServiceDesk\Models\Repositories\WorkflowRepository;
use Opscale\Validations\Validatable;

class Workflow extends Model
{
    use HasUlids, Validatable, WorkflowRepository;

    public static array $validationRules = [
        'name' => 'required|string|max:256',
        'key' => 'required|string|max:25',
    ];

    protected $table = 'service_desk_workflows';

    protected $fillable = [
        'name',
        'slug',
        'key',
        'description',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowStage::class, 'workflow_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'workflow_id');
    }
}
