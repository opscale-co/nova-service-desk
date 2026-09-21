<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Services\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Opscale\Actions\Action;
use Opscale\NovaServiceDesk\Models\Account;
use Opscale\NovaServiceDesk\Models\Task;

class CalculateDueDate extends Action
{
    public function identifier(): string
    {
        return 'calculate-due-date';
    }

    public function name(): string
    {
        return 'Calculate Due Date';
    }

    public function description(): string
    {
        return 'Calculates the due date for a task based on the SLA policy and service time';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'task',
                'description' => 'The task to calculate the due date for',
                'type' => Task::class,
                'rules' => ['required'],
            ],
        ];
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'due_date',
                'description' => 'The calculated due date for the task',
                'type' => 'datetime',
                'rules' => ['nullable'],
            ],
        ];
    }

    public function handle(array $inputs = []): array
    {
        /** @var Task $task */
        $task = $inputs['task'];
        $request = $task->request;

        if (! $request->account_id) {
            return $this->fail('Request has no account assigned', ['due_date' => null]);
        }

        $account = Account::query()->find($request->account_id);

        if (! $account) {
            return $this->fail('Account not found', ['due_date' => null]);
        }

        $slaPolicy = $account->policies()
            ->where('priority', $task->priority)
            ->first();

        if (! $slaPolicy) {
            return $this->fail('No SLA policy found for this priority', ['due_date' => null]);
        }

        $responseTime = $slaPolicy->max_resolution_time;

        if (! $responseTime) {
            return $this->fail('SLA policy has no response time defined', ['due_date' => null]);
        }

        $dueDate = $this->calculateDueDate(
            Date::now(),
            $responseTime,
            $slaPolicy->service_time ?? [],
            $slaPolicy->service_timezone ?? config('app.timezone'),
            $slaPolicy->service_exceptions ?? []
        );

        return $this->succeed(['due_date' => $dueDate]);
    }

    /**
     * Calculate the due date considering service time.
     */
    protected function calculateDueDate(
        Carbon $startDate,
        int $responseTimeHours,
        array $serviceTime,
        string $timezone,
        array $exceptions
    ): Carbon {
        $current = $startDate->copy()->timezone($timezone);
        $remainingMinutes = $responseTimeHours * 60;

        // Parse repeatable format to day-keyed schedule
        $schedule = $this->parseServiceTime($serviceTime);
        $exceptionDays = $this->parseExceptions($exceptions);

        // If no service time defined, just add the minutes directly
        if ($schedule === []) {
            return $current->addMinutes($remainingMinutes);
        }

        while ($remainingMinutes > 0) {
            $dayName = strtolower($current->format('l'));
            $dateString = $current->format('Y-m-d');

            // Check if current day is an exception
            if (in_array($dayName, $exceptionDays)) {
                $current->addDay()->startOfDay();

                continue;
            }

            // Get service hours for current day
            $daySchedule = $schedule[$dayName] ?? null;

            if (! $daySchedule || empty($daySchedule['start_time']) || empty($daySchedule['end_time'])) {
                $current->addDay()->startOfDay();

                continue;
            }

            $serviceStart = Date::parse($dateString.' '.$daySchedule['start_time'], $timezone);
            $serviceEnd = Date::parse($dateString.' '.$daySchedule['end_time'], $timezone);

            // If current time is before service start, move to service start
            if ($current->lt($serviceStart)) {
                $current = $serviceStart->copy();
            }

            // If current time is after service end, move to next day
            if ($current->gte($serviceEnd)) {
                $current->addDay()->startOfDay();

                continue;
            }

            // Calculate available minutes until service end
            $availableMinutes = $current->diffInMinutes($serviceEnd);

            if ($availableMinutes >= $remainingMinutes) {
                $current->addMinutes($remainingMinutes);
                $remainingMinutes = 0;
            } else {
                $remainingMinutes -= $availableMinutes;
                $current->addDay()->startOfDay();
            }
        }

        return $current;
    }

    /**
     * Parse repeatable service time format to day-keyed schedule.
     */
    protected function parseServiceTime(array $serviceTime): array
    {
        $schedule = [];

        foreach ($serviceTime as $slot) {
            $fields = $slot['fields'] ?? [];
            $day = $fields['day'] ?? null;

            if ($day) {
                $schedule[$day] = [
                    'start_time' => $fields['start_time'] ?? null,
                    'end_time' => $fields['end_time'] ?? null,
                ];
            }
        }

        return $schedule;
    }

    /**
     * Parse repeatable exceptions format to array of exception days.
     */
    protected function parseExceptions(array $exceptions): array
    {
        $exceptionDays = [];

        foreach ($exceptions as $exception) {
            $fields = $exception['fields'] ?? [];
            $day = $fields['day'] ?? null;

            if ($day) {
                $exceptionDays[] = $day;
            }
        }

        return $exceptionDays;
    }
}
