<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $clients = $request->user()->clients()->paginate(10);

        return response()->json($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'contact_person' => 'required|string|max:255',
        ]);

        $client = $request->user()->clients()->create($validated);

        return response()->json($client, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Client $client): JsonResponse
    {
        // Check if the authenticated user owns the client
        if ($request->user()->id !== $client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client): JsonResponse
    {
        // Check if the authenticated user owns the client
        if ($request->user()->id !== $client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255',
            'contact_person' => 'sometimes|required|string|max:255',
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Client $client): Response
    {
        // Check if the authenticated user owns the client
        if ($request->user()->id !== $client->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $client->delete();

        return response()->noContent();
    }
} 