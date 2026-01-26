<?php

namespace Opscale\NovaServiceDesk\Services\Actions;

use Opscale\Actions\Action;
use Opscale\NovaServiceDesk\Models\Category;

class GetSubcategorySequence extends Action
{
    public function identifier(): string
    {
        return 'get-subcategory-sequence';
    }

    public function name(): string
    {
        return 'Get Subcategory Sequence';
    }

    public function description(): string
    {
        return 'Generates the next sequential key for a subcategory based on the category key';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'category_id',
                'description' => 'The ID of the category',
                'type' => 'string',
                'rules' => ['required', 'string', 'exists:catalogs,id'],
            ],
        ];
    }

    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        $category = Category::find($validatedData['category_id']);

        if (! $category) {
            return [
                'success' => false,
                'sequence' => null,
            ];
        }

        $categoryKey = $category->key;

        if (! $categoryKey) {
            return [
                'success' => false,
                'sequence' => null,
            ];
        }

        $count = $category->subcategories()->count();
        $correlative = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        $sequence = $categoryKey . '-' . $correlative;

        return [
            'success' => true,
            'sequence' => $sequence,
        ];
    }
}
