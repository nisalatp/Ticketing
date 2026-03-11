<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'assignment_strategy' => 'required|string|in:manual,round_robin,workload',
        ]);

        Queue::create($validated);

        return back()->with('success', 'Queue created successfully.');
    }

    public function destroy(Queue $queue)
    {
        $queue->delete();
        return back()->with('success', 'Queue deleted successfully.');
    }
}
