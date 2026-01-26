<?php

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

    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        $template = Template::find($validatedData['template_id']);

        if (! $template) {
            return [
                'success' => false,
                'sequence' => null,
            ];
        }

        $identifier = $template->identifier;

        if (! $identifier) {
            return [
                'success' => false,
                'sequence' => null,
            ];
        }

        $count = $template->categories()->count();
        $correlative = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        $sequence = $identifier . '-' . $correlative;

        return [
            'success' => true,
            'sequence' => $sequence,
        ];
    }
}
