<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Contracts;

interface CanTransition
{
    /**
     * Check if the current status can transition to the given status.
     */
    public function canTransitionTo(self $status): bool;
}
