<?php

declare(strict_types=1);

namespace Workbench\App\Resolvers;

use Opscale\NovaServiceDesk\Contracts\ProvidesService;
use Opscale\NovaServiceDesk\Contracts\RequiresService;
use Workbench\App\Models\Department;
use Workbench\App\Models\User;

/**
 * Single implementation that bridges the abstract service desk contracts
 * to the concrete workbench domain models.
 *
 * The contracts {@see RequiresService} and {@see ProvidesService} are NOT
 * marker interfaces meant to be inherited by Eloquent models. They are
 * strategy contracts that an application implements once and binds in the
 * service container; package code (e.g. AssignTask, the Account Nova
 * resource) resolves them through the container instead of scanning models.
 *
 * In the workbench:
 *   - Departments REQUIRE service (printers, mailboxes, processes…)
 *   - Users PROVIDE service (the agents that work on tickets)
 */
class ServiceResolver implements ProvidesService, RequiresService
{
    /**
     * Entities apt to have tasks placed on them.
     *
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    public function servedEntities(): array
    {
        return Department::query()->get()->all();
    }

    /**
     * Agents that actually deliver the service.
     *
     * @return array<int, \Illuminate\Database\Eloquent\Model>
     */
    public function servingAgents(): array
    {
        return User::query()->get()->all();
    }
}
