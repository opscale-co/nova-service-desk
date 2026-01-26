<?php

namespace Opscale\NovaServiceDesk\Contracts;

use Opscale\NovaServiceDesk\Models\Task;

interface PriorityScoreResolver
{
    /**
     * Get the priority score for a task.
     */
    public function getScore(Task $task): float;
}
