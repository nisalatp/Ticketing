<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TopicController extends Controller
{
    public function index()
    {
        $topics = Topic::with('department')->orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();

        return Inertia::render('Configuration/Topics/Index', [
            'topics' => $topics,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
        ]);

        Topic::create($request->all());

        return redirect()->back()->with('message', 'Topic created successfully.');
    }

    public function update(Request $request, Topic $topic)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $topic->update($request->all());

        return redirect()->back()->with('message', 'Topic updated successfully.');
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();
        return redirect()->back()->with('message', 'Topic deleted successfully.');
    }
}
