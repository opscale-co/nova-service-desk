<?php

declare(strict_types=1);

use Opscale\NovaServiceDesk\Models\Enums\InsightScope;
use Opscale\NovaServiceDesk\Models\Enums\ServiceChannel;
use Opscale\NovaServiceDesk\Models\Enums\SLAPolicyStatus;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;

it('has the correct InsightScope cases', function (): void {
    expect(InsightScope::cases())->toHaveCount(5);
    expect(InsightScope::Business->value)->toBe('Business');
    expect(InsightScope::Technical->value)->toBe('Technical');
    expect(InsightScope::Legal->value)->toBe('Legal');
    expect(InsightScope::Operational->value)->toBe('Operational');
    expect(InsightScope::Other->value)->toBe('Other');
});

it('has the correct SLAPolicyStatus cases', function (): void {
    expect(SLAPolicyStatus::cases())->toHaveCount(2);
    expect(SLAPolicyStatus::Active->value)->toBe('Active');
    expect(SLAPolicyStatus::Inactive->value)->toBe('Inactive');
});

it('has the correct SLAPriority cases', function (): void {
    expect(SLAPriority::cases())->toHaveCount(5);
    expect(SLAPriority::Critical->value)->toBe('Critical');
    expect(SLAPriority::High->value)->toBe('High');
    expect(SLAPriority::Medium->value)->toBe('Medium');
    expect(SLAPriority::Low->value)->toBe('Low');
    expect(SLAPriority::Planning->value)->toBe('Planning');
});

it('has the correct ServiceChannel cases', function (): void {
    expect(ServiceChannel::cases())->toHaveCount(4);
    expect(ServiceChannel::Web->value)->toBe('Web');
    expect(ServiceChannel::Chat->value)->toBe('Chat');
    expect(ServiceChannel::Email->value)->toBe('Email');
    expect(ServiceChannel::Phone->value)->toBe('Phone');
});
