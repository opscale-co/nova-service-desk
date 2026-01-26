<?php

namespace Opscale\NovaServiceDesk\Models\Enums;

use Opscale\NovaServiceDesk\Contracts\CanTransition;

enum TaskStatus: string implements CanTransition
{
    case Open = 'Open';
    case InProgress = 'In Progress';
    case Blocked = 'Blocked';
    case Resolved = 'Resolved';
    case Closed = 'Closed';
    case Cancelled = 'Cancelled';

    /**
     * Check if the current status can transition to the given status.
     */
    public function canTransitionTo(CanTransition $status): bool
    {
        return in_array($status, $this->allowedTransitions());
    }

    /**
     * Get the allowed transitions for the current status.
     *
     * @return array<TaskStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Blocked, self::Resolved, self::Cancelled],
            self::Blocked => [self::InProgress, self::Cancelled],
            self::Resolved => [self::Closed, self::InProgress],
            self::Closed => [],
            self::Cancelled => [],
        };
    }
}
