<?php

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use Opscale\NovaServiceDesk\Models\Category;
use Opscale\NovaServiceDesk\Models\Subcategory;
use Opscale\NovaServiceDesk\Services\Actions\GetSubcategorySequence;

it('generates the first sequence for a category', function (): void {
    $category = Category::create([
        'name' => 'IT Support',
        'key' => 'ITS',
    ]);

    $result = GetSubcategorySequence::run([
        'category_id' => $category->id,
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['sequence'])->toBe('ITS-01');
});

it('increments the sequence for existing subcategories', function (): void {
    $category = Category::create([
        'name' => 'IT Support',
        'key' => 'ITS',
    ]);

    Subcategory::create([
        'category_id' => $category->id,
        'name' => 'Hardware',
        'key' => 'ITS-01',
    ]);

    $result = GetSubcategorySequence::run([
        'category_id' => $category->id,
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['sequence'])->toBe('ITS-02');
});

it('throws validation error when category does not exist', function (): void {
    GetSubcategorySequence::run([
        'category_id' => 'non-existent-id',
    ]);
})->throws(ValidationException::class);
