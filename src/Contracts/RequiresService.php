<?php

namespace Opscale\NovaServiceDesk\Contracts;

interface RequiresService
{
    /**
     * Get the users serving this entity.
     */
    public function servingUsers(): array;
}
