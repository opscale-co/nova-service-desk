<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Contracts;

/**
 * Marker contract for models that REQUEST service — Customers, Accounts,
 * Devices and any entity that can have tasks placed on it.
 *
 * Counterpart of {@see ProvidesService}, which is implemented by the
 * agents/staff that work on those tasks.
 */
interface RequiresService
{
    /**
     * Get the entities that are apt to have tasks placed on them.
     *
     * For a self-implementing model (e.g. a Customer that represents
     * itself), return [$this]. For aggregate models (e.g. a Company
     * with multiple branches), return all the underlying entities that
     * can be the target of a task.
     *
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    public function servedEntities(): array;
}
