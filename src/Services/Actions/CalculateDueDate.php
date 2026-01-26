<?php

namespace Opscale\NovaServiceDesk\Services\Actions;

use Carbon\Carbon;
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

    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        /** @var Task $task */
        $task = $validatedData['task'];
        $request = $task->request;

        if (! $request->account_id) {
            return [
                'success' => false,
                'due_date' => null,
                'message' => 'Request has no account assigned',
            ];
        }

        $account = Account::find($request->account_id);

        if (! $account) {
            return [
                'success' => false,
                'due_date' => null,
                'message' => 'Account not found',
            ];
        }

        $slaPolicy = $account->policies()
            ->where('priority', $task->priority)
            ->first();

        if (! $slaPolicy) {
            return [
                'success' => false,
                'due_date' => null,
                'message' => 'No SLA policy found for this priority',
            ];
        }

        $responseTime = $slaPolicy->max_resolution_time;

        if (! $responseTime) {
            return [
                'success' => false,
                'due_date' => null,
                'message' => 'SLA policy has no response time defined',
            ];
        }

        $dueDate = $this->calculateDueDate(
            Carbon::now(),
            $responseTime,
            $slaPolicy->service_time ?? [],
            $slaPolicy->service_timezone ?? config('app.timezone'),
            $slaPolicy->service_exceptions ?? []
        );

        return [
            'success' => true,
            'due_date' => $dueDate,
        ];
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
        if (empty($schedule)) {
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

            $serviceStart = Carbon::parse($dateString . ' ' . $daySchedule['start_time'], $timezone);
            $serviceEnd = Carbon::parse($dateString . ' ' . $daySchedule['end_time'], $timezone);

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

        foreach ($exceptions as $slot) {
            $fields = $slot['fields'] ?? [];
            $day = $fields['day'] ?? null;

            if ($day) {
                $exceptionDays[] = $day;
            }
        }

        return $exceptionDays;
    }
}
