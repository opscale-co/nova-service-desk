<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Contracts;

/**
 * Marker contract for models that PROVIDE service — agents, technicians,
 * staff users that work on tickets.
 *
 * Counterpart of {@see RequiresService}, which is implemented by the
 * customer entities that REQUEST service.
 */
interface ProvidesService
{
    /**
     * Get the agent(s) that actually deliver the service.
     *
     * For a self-implementing model (e.g. a User that represents an
     * agent), return [$this]. For aggregate models (e.g. a Team), return
     * all the underlying agent users.
     *
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    public function servingAgents(): array;
}
