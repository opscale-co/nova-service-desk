<?php

namespace Opscale\NovaServiceDesk\Models\Rules;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Opscale\NovaServiceDesk\Contracts\CanTransition;

class StatusRule implements ValidationRule
{
    /**
     * @param  Model  $model  The model instance to check current status from
     * @param  class-string<BackedEnum&CanTransition>  $enumClass  The enum class implementing CanTransition
     */
    public function __construct(
        protected Model $model,
        protected string $enumClass,
    ) {
        if (! is_a($enumClass, CanTransition::class, true)) {
            throw new InvalidArgumentException("Enum class {$enumClass} must implement CanTransition interface.");
        }
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currentValue = $this->model->{$attribute};

        if ($currentValue === null) {
            return;
        }

        $currentStatus = $currentValue instanceof BackedEnum
            ? $currentValue
            : $this->enumClass::from($currentValue);

        $newStatus = $value instanceof BackedEnum
            ? $value
            : $this->enumClass::from($value);

        $templateKey = strtoupper(substr($this->model->key, 0, 3));
        $resolvers = config('nova-service-desk.custom_statuses_resolvers', []);

        if (isset($resolvers[$templateKey])) {
            $resolver = app($resolvers[$templateKey]);
            $canTransition = $resolver->canTransitionTo($this->model, $newStatus->value);
        } else {
            $canTransition = $currentStatus->canTransitionTo($newStatus);
        }

        if (! $canTransition) {
            $fail("Cannot transition from {$currentStatus->value} to {$newStatus->value}.");
        }
    }
}
