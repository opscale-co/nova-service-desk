<?php

declare(strict_types=1);

use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;

it('allows Open to transition to InProgress and Cancelled', function (): void {
    $status = TaskStatus::Open;
    $allowed = $status->allowedTransitions();

    expect($allowed)->toContain(TaskStatus::InProgress)
        ->toContain(TaskStatus::Cancelled)
        ->toHaveCount(2);
});

it('allows InProgress to transition to Blocked, Resolved, and Cancelled', function (): void {
    $status = TaskStatus::InProgress;
    $allowed = $status->allowedTransitions();

    expect($allowed)->toContain(TaskStatus::Blocked)
        ->toContain(TaskStatus::Resolved)
        ->toContain(TaskStatus::Cancelled)
        ->toHaveCount(3);
});

it('allows Blocked to transition to InProgress and Cancelled', function (): void {
    $status = TaskStatus::Blocked;
    $allowed = $status->allowedTransitions();

    expect($allowed)->toContain(TaskStatus::InProgress)
        ->toContain(TaskStatus::Cancelled)
        ->toHaveCount(2);
});

it('allows Resolved to transition to Closed and InProgress', function (): void {
    $status = TaskStatus::Resolved;
    $allowed = $status->allowedTransitions();

    expect($allowed)->toContain(TaskStatus::Closed)
        ->toContain(TaskStatus::InProgress)
        ->toHaveCount(2);
});

it('does not allow Closed to transition', function (): void {
    expect(TaskStatus::Closed->allowedTransitions())->toBeEmpty();
});

it('does not allow Cancelled to transition', function (): void {
    expect(TaskStatus::Cancelled->allowedTransitions())->toBeEmpty();
});

it('validates canTransitionTo returns true for valid transitions', function (): void {
    expect(TaskStatus::Open->canTransitionTo(TaskStatus::InProgress))->toBeTrue();
    expect(TaskStatus::InProgress->canTransitionTo(TaskStatus::Resolved))->toBeTrue();
    expect(TaskStatus::Resolved->canTransitionTo(TaskStatus::Closed))->toBeTrue();
});

it('validates canTransitionTo returns false for invalid transitions', function (): void {
    expect(TaskStatus::Open->canTransitionTo(TaskStatus::Closed))->toBeFalse();
    expect(TaskStatus::Closed->canTransitionTo(TaskStatus::Open))->toBeFalse();
    expect(TaskStatus::Cancelled->canTransitionTo(TaskStatus::Open))->toBeFalse();
});
