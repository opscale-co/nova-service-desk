<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Services\Actions;

use Opscale\Actions\Action;
use Opscale\NovaServiceDesk\Models\Template;

class GetCategorySequence extends Action
{
    public function identifier(): string
    {
        return 'get-category-sequence';
    }

    public function name(): string
    {
        return 'Get Category Sequence';
    }

    public function description(): string
    {
        return 'Generates the next sequential key for a category based on the template identifier';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'template_id',
                'description' => 'The ID of the template',
                'type' => 'string',
                'rules' => ['required', 'string', 'exists:dynamic_resources,id'],
            ],
        ];
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'sequence',
                'description' => 'The next sequential key for the category',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
        ];
    }

    public function handle(array $inputs = []): array
    {
        $template = Template::query()->find($inputs['template_id']);

        if (! $template) {
            return $this->fail('Template not found', ['sequence' => null]);
        }

        $identifier = $template->identifier;

        if (! $identifier) {
            return $this->fail('Template has no identifier', ['sequence' => null]);
        }

        $lastCategory = $template->categories()->withoutGlobalScopes()->orderByDesc('key')->first();
        $lastNumber = $lastCategory ? (int) substr($lastCategory->key, strrpos($lastCategory->key, '-') + 1) : 0;
        $correlative = str_pad((string) ($lastNumber + 1), 2, '0', STR_PAD_LEFT);
        $sequence = $identifier.'-'.$correlative;

        return $this->succeed(['sequence' => $sequence]);
    }
}
