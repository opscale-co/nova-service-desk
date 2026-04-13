<?php

declare(strict_types=1);

use Opscale\NovaServiceDesk\Models\Workflow;
use Opscale\NovaServiceDesk\Models\WorkflowStage;

it('has the correct table name', function (): void {
    $workflow = new Workflow;
    expect($workflow->getTable())->toBe('service_desk_workflows');
});

it('has the correct fillable attributes', function (): void {
    $workflow = new Workflow;
    expect($workflow->getFillable())->toBe([
        'name',
        'slug',
        'key',
        'description',
    ]);
});

it('defines validation rules', function (): void {
    expect(Workflow::$validationRules)->toHaveKeys(['name', 'key']);
    expect(Workflow::$validationRules['name'])->toContain('required');
    expect(Workflow::$validationRules['key'])->toContain('required');
});

it('has validation rules for WorkflowStage', function (): void {
    expect(WorkflowStage::$validationRules)->toHaveKeys([
        'workflow_id',
        'name',
        'maps_to_status',
    ]);
});
