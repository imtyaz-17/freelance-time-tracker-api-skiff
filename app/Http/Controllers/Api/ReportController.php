<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\TimeLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ReportController extends Controller
{
    /**
     * Generate a report of time logs.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'group_by' => 'nullable|in:day,week,month,project,client',
        ]);

        $query = TimeLog::whereHas('project.client', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->with('project.client');

        // Apply date range filter
        $query->whereBetween('start_time', [
            Carbon::parse($validated['from'])->startOfDay(),
            Carbon::parse($validated['to'])->endOfDay(),
        ]);

        // Apply client filter if provided
        if (isset($validated['client_id'])) {
            // Verify that the client belongs to the authenticated user
            $client = Client::findOrFail($validated['client_id']);
            if ($request->user()->id !== $client->user_id) {
                return response()->json([
                    'message' => 'You are not authorized to access reports for this client'
                ], 403);
            }

            $query->whereHas('project', function ($query) use ($validated) {
                $query->where('client_id', $validated['client_id']);
            });
        }

        // Apply project filter if provided
        if (isset($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }

        // Group by the specified parameter if provided
        $groupBy = $validated['group_by'] ?? 'day';
        
        switch ($groupBy) {
            case 'day':
                $results = $this->groupByDay($query);
                break;
            case 'week':
                $results = $this->groupByWeek($query);
                break;
            case 'month':
                $results = $this->groupByMonth($query);
                break;
            case 'project':
                $results = $this->groupByProject($query);
                break;
            case 'client':
                $results = $this->groupByClient($query);
                break;
            default:
                $results = $this->groupByDay($query);
        }

        return response()->json([
            'message' => 'Report generated successfully',
            'data' => [
                'from' => $validated['from'],
                'to' => $validated['to'],
                'group_by' => $groupBy,
                'results' => $results,
            ]
        ]);
    }

    /**
     * Group time logs by day.
     */
    private function groupByDay($query): array
    {
        $timeLogs = clone $query;
        $timeLogs = $timeLogs->get();

        $results = [];
        foreach ($timeLogs as $timeLog) {
            $date = Carbon::parse($timeLog->start_time)->format('Y-m-d');
            if (!isset($results[$date])) {
                $results[$date] = [
                    'date' => $date,
                    'total_hours' => 0,
                    'time_logs' => [],
                ];
            }
            $results[$date]['total_hours'] += $timeLog->hours;
            $results[$date]['time_logs'][] = $timeLog;
        }

        return array_values($results);
    }

    /**
     * Group time logs by week.
     */
    private function groupByWeek($query): array
    {
        $timeLogs = clone $query;
        $timeLogs = $timeLogs->get();

        $results = [];
        foreach ($timeLogs as $timeLog) {
            $date = Carbon::parse($timeLog->start_time);
            $weekStart = $date->copy()->startOfWeek()->format('Y-m-d');
            $weekEnd = $date->copy()->endOfWeek()->format('Y-m-d');
            $weekKey = "{$weekStart} to {$weekEnd}";

            if (!isset($results[$weekKey])) {
                $results[$weekKey] = [
                    'week' => $weekKey,
                    'week_start' => $weekStart,
                    'week_end' => $weekEnd,
                    'total_hours' => 0,
                    'time_logs' => [],
                ];
            }
            $results[$weekKey]['total_hours'] += $timeLog->hours;
            $results[$weekKey]['time_logs'][] = $timeLog;
        }

        return array_values($results);
    }

    /**
     * Group time logs by month.
     */
    private function groupByMonth($query): array
    {
        $timeLogs = clone $query;
        $timeLogs = $timeLogs->get();

        $results = [];
        foreach ($timeLogs as $timeLog) {
            $month = Carbon::parse($timeLog->start_time)->format('Y-m');

            if (!isset($results[$month])) {
                $results[$month] = [
                    'month' => $month,
                    'total_hours' => 0,
                    'time_logs' => [],
                ];
            }
            $results[$month]['total_hours'] += $timeLog->hours;
            $results[$month]['time_logs'][] = $timeLog;
        }

        return array_values($results);
    }

    /**
     * Group time logs by project.
     */
    private function groupByProject($query): array
    {
        $timeLogs = clone $query;
        $timeLogs = $timeLogs->get();

        $results = [];
        foreach ($timeLogs as $timeLog) {
            $projectId = $timeLog->project_id;
            $projectTitle = $timeLog->project->title;

            if (!isset($results[$projectId])) {
                $results[$projectId] = [
                    'project_id' => $projectId,
                    'project_title' => $projectTitle,
                    'total_hours' => 0,
                    'time_logs' => [],
                ];
            }
            $results[$projectId]['total_hours'] += $timeLog->hours;
            $results[$projectId]['time_logs'][] = $timeLog;
        }

        return array_values($results);
    }

    /**
     * Group time logs by client.
     */
    private function groupByClient($query): array
    {
        $timeLogs = clone $query;
        $timeLogs = $timeLogs->get();

        $results = [];
        foreach ($timeLogs as $timeLog) {
            $clientId = $timeLog->project->client_id;
            $clientName = $timeLog->project->client->name;

            if (!isset($results[$clientId])) {
                $results[$clientId] = [
                    'client_id' => $clientId,
                    'client_name' => $clientName,
                    'total_hours' => 0,
                    'time_logs' => [],
                ];
            }
            $results[$clientId]['total_hours'] += $timeLog->hours;
            $results[$clientId]['time_logs'][] = $timeLog;
        }

        return array_values($results);
    }

    /**
     * Generate a PDF report of time logs.
     */
    public function exportPdf(Request $request): Response
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'group_by' => 'nullable|in:day,week,month,project,client',
        ]);

        $query = TimeLog::whereHas('project.client', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->with('project.client');

        // Apply date range filter
        $query->whereBetween('start_time', [
            Carbon::parse($validated['from'])->startOfDay(),
            Carbon::parse($validated['to'])->endOfDay(),
        ]);

        // Apply client filter if provided
        if (isset($validated['client_id'])) {
            // Verify that the client belongs to the authenticated user
            $client = Client::findOrFail($validated['client_id']);
            if ($request->user()->id !== $client->user_id) {
                return response([
                    'message' => 'You are not authorized to access reports for this client'
                ], 403);
            }

            $query->whereHas('project', function ($query) use ($validated) {
                $query->where('client_id', $validated['client_id']);
            });
        }

        // Apply project filter if provided
        if (isset($validated['project_id'])) {
            $project = Project::findOrFail($validated['project_id']);
            $query->where('project_id', $validated['project_id']);
        }

        // Group by the specified parameter if provided
        $groupBy = $validated['group_by'] ?? 'day';
        
        switch ($groupBy) {
            case 'day':
                $results = $this->groupByDay($query);
                break;
            case 'week':
                $results = $this->groupByWeek($query);
                break;
            case 'month':
                $results = $this->groupByMonth($query);
                break;
            case 'project':
                $results = $this->groupByProject($query);
                break;
            case 'client':
                $results = $this->groupByClient($query);
                break;
            default:
                $results = $this->groupByDay($query);
        }

        // Format dates for the PDF title
        $fromDate = Carbon::parse($validated['from'])->format('Y-m-d');
        $toDate = Carbon::parse($validated['to'])->format('Y-m-d');
        
        // Set the title of the report
        $title = "Time Logs Report - {$fromDate} to {$toDate}";
        
        // Get client or project name if filtering
        $subtitle = '';
        if (isset($validated['client_id']) && isset($client)) {
            $subtitle .= "Client: {$client->name}";
        }
        if (isset($validated['project_id']) && isset($project)) {
            $subtitle .= (empty($subtitle) ? "" : " | ") . "Project: {$project->title}";
        }
        
        // Calculate total hours
        $totalHours = 0;
        foreach ($results as $result) {
            $totalHours += abs($result['total_hours']);
        }
        
        // Generate PDF
        $pdf = PDF::loadView('reports.time-logs-pdf', [
            'title' => $title,
            'subtitle' => $subtitle,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'groupBy' => $groupBy,
            'results' => $results,
            'totalHours' => $totalHours,
            'user' => $request->user(),
            'generatedAt' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        
        // Set PDF options
        $pdf->setPaper('a4', 'landscape');
        
        // Generate filename
        $filename = "time_logs_{$fromDate}_to_{$toDate}.pdf";
        
        // Download the PDF file
        return $pdf->download($filename);
    }
} 