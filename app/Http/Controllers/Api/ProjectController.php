<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Get all projects that belong to the user's clients
        $projects = Project::whereHas('client', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->with('client')->paginate(10);

        return response()->json($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:active,completed',
            'deadline' => 'nullable|date',
        ]);

        // Verify that the client belongs to the authenticated user
        $client = Client::findOrFail($validated['client_id']);
        if ($request->user()->id !== $client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project = Project::create($validated);

        return response()->json($project, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        // Check if the authenticated user owns the client that the project belongs to
        if ($request->user()->id !== $project->client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($project->load('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        // Check if the authenticated user owns the client that the project belongs to
        if ($request->user()->id !== $project->client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'client_id' => 'sometimes|required|exists:clients,id',
            'status' => 'sometimes|required|in:active,completed',
            'deadline' => 'nullable|date',
        ]);

        // If client_id is being updated, verify that the new client belongs to the authenticated user
        if (isset($validated['client_id'])) {
            $client = Client::findOrFail($validated['client_id']);
            if ($request->user()->id !== $client->user_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $project->update($validated);

        return response()->json($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project): Response
    {
        // Check if the authenticated user owns the client that the project belongs to
        if ($request->user()->id !== $project->client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->noContent();
    }
} 