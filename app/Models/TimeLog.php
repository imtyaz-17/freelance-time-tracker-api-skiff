<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TimeLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'start_time',
        'end_time',
        'description',
        'hours',
        'is_billable',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'hours' => 'float',
        'is_billable' => 'boolean',
    ];

    /**
     * Get the project that owns the time log.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Calculate hours based on start and end time
     */
    public function calculateHours(): void
    {
        if ($this->start_time && $this->end_time) {
            $this->hours = $this->start_time->diffInSeconds($this->end_time) / 3600;
        }
    }
} 