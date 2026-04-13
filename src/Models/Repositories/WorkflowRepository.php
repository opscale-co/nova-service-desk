<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models\Repositories;

use Illuminate\Support\Collection;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\WorkflowStage;

trait WorkflowRepository
{
    /**
     * Look up a workflow by key matching a template key.
     */
    public static function resolveForTemplate(string $templateKey): ?static
    {
        return static::where('key', $templateKey)->first();
    }

    /**
     * Get the initial stage for this workflow (first stage created).
     */
    public function initialStage(): ?WorkflowStage
    {
        return $this->stages()->oldest()->first();
    }

    /**
     * Get stages mapped to a specific master status.
     */
    public function stagesForStatus(TaskStatus $status): Collection
    {
        return $this->stages()->where('maps_to_status', $status->value)->get();
    }
}
