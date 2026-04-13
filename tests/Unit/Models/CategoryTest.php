<?php

declare(strict_types=1);

use Opscale\NovaServiceDesk\Models\Category;

it('has the correct table name', function (): void {
    $category = new Category;
    expect($category->getTable())->toBe('service_desk_categories');
});

it('has the correct fillable attributes', function (): void {
    $category = new Category;
    expect($category->getFillable())->toBe([
        'name',
        'key',
        'description',
        'impact_options',
        'urgency_options',
    ]);
});

it('defines validation rules', function (): void {
    expect(Category::$validationRules)->toHaveKeys([
        'description',
        'name',
        'key',
        'impact_options',
        'urgency_options',
    ]);
    expect(Category::$validationRules['name'])->toContain('required');
});
