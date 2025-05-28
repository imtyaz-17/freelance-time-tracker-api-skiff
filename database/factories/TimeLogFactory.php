<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeLog>
 */
class TimeLogFactory extends Factory
{
    protected $model = TimeLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('-1 month', 'now');
        $endTime = (clone $startTime)->modify('+' . $this->faker->numberBetween(1, 8) . ' hours');
        $hours = Carbon::parse($startTime)->diffInSeconds(Carbon::parse($endTime)) / 3600;

        return [
            'project_id' => Project::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'description' => $this->faker->sentence,
            'hours' => round($hours, 2),
            'is_billable' => $this->faker->boolean(80), // 80% chance of being billable
        ];
    }

    /**
     * Indicate that the time log is billable.
     */
    public function billable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_billable' => true,
        ]);
    }

    /**
     * Indicate that the time log is not billable.
     */
    public function nonBillable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_billable' => false,
        ]);
    }

    /**
     * Create a time log that's currently running (no end time).
     */
    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_time' => null,
            'hours' => null,
        ]);
    }
} 