<?php

declare(strict_types=1);

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
                'rules' => ['required', 'string', 'exists:service_desk_categories,id'],
            ],
        ];
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'sequence',
                'description' => 'The next sequential key for the subcategory',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
        ];
    }

    public function handle(array $inputs = []): array
    {
        $category = Category::query()->find($inputs['category_id']);

        if (! $category) {
            return $this->fail('Category not found', ['sequence' => null]);
        }

        $categoryKey = $category->key;

        if (! $categoryKey) {
            return $this->fail('Category has no key', ['sequence' => null]);
        }

        $lastSubcategory = $category->subcategories()->withoutGlobalScopes()->orderByDesc('key')->first();
        $lastNumber = $lastSubcategory ? (int) substr($lastSubcategory->key, strrpos($lastSubcategory->key, '-') + 1) : 0;
        $correlative = str_pad((string) ($lastNumber + 1), 2, '0', STR_PAD_LEFT);
        $sequence = $categoryKey.'-'.$correlative;

        return $this->succeed(['sequence' => $sequence]);
    }
}
