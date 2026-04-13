<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Services\Actions;

use Opscale\Actions\Action;
use Opscale\NovaServiceDesk\Models\Subcategory;
use Opscale\NovaServiceDesk\Models\Task;

class GetTaskSequence extends Action
{
    public function identifier(): string
    {
        return 'get-task-sequence';
    }

    public function name(): string
    {
        return 'Get Task Sequence';
    }

    public function description(): string
    {
        return 'Generates the next sequential key for a task based on the subcategory identifier';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'subcategory_id',
                'description' => 'The ID of the subcategory',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
        ];
    }

    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        $subcategory = Subcategory::find($validatedData['subcategory_id']);

        if (! $subcategory) {
            return [
                'success' => false,
                'sequence' => null,
            ];
        }

        $identifier = $subcategory->key;

        if (! $identifier) {
            return [
                'success' => false,
                'sequence' => null,
            ];
        }

        $lastTask = Task::whereHas('request', fn ($query) => $query->where('subcategory_id', $subcategory->id))
            ->orderByDesc('key')
            ->first();
        $lastNumber = $lastTask ? (int) substr($lastTask->key, strrpos($lastTask->key, '-') + 1) : 0;
        $correlative = str_pad((string) ($lastNumber + 1), 6, '0', STR_PAD_LEFT);
        $sequence = $identifier.'-'.$correlative;

        return [
            'success' => true,
            'sequence' => $sequence,
        ];
    }
}
