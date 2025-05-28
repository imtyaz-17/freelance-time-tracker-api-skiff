<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class TimeLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TimeLog::whereHas('project.client', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->with('project.client');

        // Filter by date range
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('start_time', [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->to)->endOfDay(),
            ]);
        } elseif ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $query->whereBetween('start_time', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ]);
        }

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $timeLogs = $query->orderBy('start_time', 'desc')->paginate(10);

        return response()->json($timeLogs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'description' => 'required|string',
            'is_billable' => 'boolean',
        ]);

        // Verify that the project belongs to the authenticated user
        $project = Project::findOrFail($validated['project_id']);
        if ($request->user()->id !== $project->client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $timeLog = new TimeLog($validated);
        $timeLog->calculateHours();
        $timeLog->save();

        return response()->json($timeLog, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, TimeLog $timeLog): JsonResponse
    {
        // Check if the authenticated user owns the client that the project belongs to
        if ($request->user()->id !== $timeLog->project->client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($timeLog->load('project.client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimeLog $timeLog): JsonResponse
    {
        // Check if the authenticated user owns the client that the project belongs to
        if ($request->user()->id !== $timeLog->project->client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'project_id' => 'sometimes|required|exists:projects,id',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
            'description' => 'sometimes|required|string',
            'is_billable' => 'boolean',
        ]);

        // If project_id is being updated, verify that the new project belongs to the authenticated user
        if (isset($validated['project_id'])) {
            $project = Project::findOrFail($validated['project_id']);
            if ($request->user()->id !== $project->client->user_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $timeLog->fill($validated);
        $timeLog->calculateHours();
        $timeLog->save();

        return response()->json($timeLog);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, TimeLog $timeLog): Response
    {
        // Check if the authenticated user owns the client that the project belongs to
        if ($request->user()->id !== $timeLog->project->client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $timeLog->delete();

        return response()->noContent();
    }

    /**
     * Start a timer for a project.
     */
    public function startTimer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'description' => 'required|string',
            'is_billable' => 'boolean',
        ]);

        // Verify that the project belongs to the authenticated user
        $project = Project::findOrFail($validated['project_id']);
        if ($request->user()->id !== $project->client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if there's already an active timer
        $activeTimer = TimeLog::whereNull('end_time')->first();
        if ($activeTimer) {
            return response()->json([
                'message' => 'You already have an active timer',
                'timer' => $activeTimer,
            ], 422);
        }

        $timeLog = new TimeLog([
            'project_id' => $validated['project_id'],
            'description' => $validated['description'],
            'start_time' => Carbon::now(),
            'is_billable' => $validated['is_billable'] ?? true,
        ]);
        $timeLog->save();

        return response()->json($timeLog, 201);
    }

    /**
     * Stop a timer.
     */
    public function stopTimer(Request $request, TimeLog $timeLog): JsonResponse
    {
        // Check if the authenticated user owns the client that the project belongs to
        if ($request->user()->id !== $timeLog->project->client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if the timer is already stopped
        if ($timeLog->end_time) {
            return response()->json([
                'message' => 'This timer is already stopped',
                'timer' => $timeLog,
            ], 422);
        }

        $timeLog->end_time = Carbon::now();
        $timeLog->calculateHours();
        $timeLog->save();

        // Check if the user has logged more than 8 hours in a single day
        $this->checkDailyHours($request->user()->id, $timeLog);

        return response()->json($timeLog);
    }

    /**
     * Check if a user has logged more than 8 hours in a single day.
     */
    private function checkDailyHours(int $userId, TimeLog $timeLog): void
    {
        $date = Carbon::parse($timeLog->start_time)->format('Y-m-d');
        
        $totalHours = TimeLog::whereHas('project.client', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereDate('start_time', $date)
        ->sum('hours');

        if ($totalHours >= 8) {
            // This would be a good place to send an email notification
            // For now, we'll just log it
            \Log::info("User {$userId} has logged {$totalHours} hours on {$date}");
        }
    }
} 