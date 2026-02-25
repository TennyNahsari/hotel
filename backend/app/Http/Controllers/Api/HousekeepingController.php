<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public function index(Request $request)
    {
        $query = HousekeepingTask::with(['room.roomType', 'hall', 'assignedUser']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by room
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->orderBy('priority', 'asc')
                       ->orderBy('created_at', 'desc')
                       ->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'hall_id' => 'nullable|exists:halls,id',
            'task_type' => 'required|in:cleaning,maintenance,inspection,deep_clean,hall_cleaning',
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Ensure either room_id or hall_id is provided
        if (empty($validated['room_id']) && empty($validated['hall_id'])) {
            return response()->json([
                'message' => 'Either room_id or hall_id must be provided'
            ], 422);
        }

        // Ensure only one is provided
        if (!empty($validated['room_id']) && !empty($validated['hall_id'])) {
            return response()->json([
                'message' => 'Cannot assign task to both room and hall'
            ], 422);
        }

        $task = HousekeepingTask::create([
            'room_id' => $validated['room_id'] ?? null,
            'hall_id' => $validated['hall_id'] ?? null,
            'task_type' => $validated['task_type'],
            'priority' => $validated['priority'],
            'status' => 'pending',
            'assigned_to' => $validated['assigned_to'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update hall status when task is created
        if ($task->hall_id) {
            $status = match($task->task_type) {
                'cleaning', 'hall_cleaning' => 'cleaning',
                'maintenance' => 'maintenance',
                default => 'occupied'
            };
            $task->hall->update(['status' => $status]);
        }

        return response()->json([
            'message' => 'Housekeeping task created successfully',
            'data' => $task->load(['room.roomType', 'hall', 'assignedUser'])
        ], 201);
    }

    public function show(HousekeepingTask $housekeeping)
    {
        return response()->json(
            $housekeeping->load(['room.roomType', 'hall', 'assignedUser'])
        );
    }

    public function update(Request $request, HousekeepingTask $housekeeping)
    {
        $validated = $request->validate([
            'task_type' => 'sometimes|in:cleaning,maintenance,inspection,deep_clean,hall_cleaning',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $housekeeping->update($validated);

        return response()->json([
            'message' => 'Housekeeping task updated successfully',
            'data' => $housekeeping->load(['room.roomType', 'hall', 'assignedUser'])
        ]);
    }

    public function destroy(HousekeepingTask $housekeeping)
    {
        if ($housekeeping->status === 'in_progress') {
            return response()->json([
                'message' => 'Cannot delete task that is in progress'
            ], 422);
        }

        // Update hall status back to available when task is deleted
        if ($housekeeping->hall_id) {
            $housekeeping->hall->update(['status' => 'available']);
        }

        $housekeeping->delete();

        return response()->json([
            'message' => 'Housekeeping task deleted successfully'
        ]);
    }

    public function updateStatus(Request $request, HousekeepingTask $housekeeping)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'in_progress' && !$housekeeping->started_at) {
            $updates['started_at'] = now();
            
            // Update room status to cleaning when task starts
            if ($housekeeping->room_id && $housekeeping->task_type === 'cleaning') {
                $housekeeping->room->update(['status' => 'cleaning']);
            }
            
            // Update hall status when task starts
            if ($housekeeping->hall_id) {
                $status = match($housekeeping->task_type) {
                    'cleaning', 'hall_cleaning' => 'cleaning',
                    'maintenance' => 'maintenance',
                    default => 'occupied'
                };
                $housekeeping->hall->update(['status' => $status]);
            }
        }

        if ($validated['status'] === 'completed') {
            $updates['completed_at'] = now();
            
            // Update room status based on task type
            if ($housekeeping->room_id && $housekeeping->task_type === 'cleaning') {
                $housekeeping->room->update(['status' => 'available']);
            }
            
            // Update hall status to available when task completed
            if ($housekeeping->hall_id) {
                $housekeeping->hall->update(['status' => 'available']);
            }
        }

        $housekeeping->update($updates);

        return response()->json([
            'message' => 'Task status updated successfully',
            'data' => $housekeeping->fresh(['room.roomType', 'hall', 'assignedUser'])
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total' => HousekeepingTask::count(),
            'pending' => HousekeepingTask::where('status', 'pending')->count(),
            'in_progress' => HousekeepingTask::where('status', 'in_progress')->count(),
            'completed_today' => HousekeepingTask::where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'high_priority' => HousekeepingTask::where('priority', 'high')
                ->orWhere('priority', 'urgent')
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
        ];

        return response()->json($stats);
    }
}
