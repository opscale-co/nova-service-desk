<?php

namespace Opscale\NovaServiceDesk\Contracts;

interface CanTransition
{
    /**
     * Check if the current status can transition to the given status.
     */
    public function canTransitionTo(self $status): bool;
}
