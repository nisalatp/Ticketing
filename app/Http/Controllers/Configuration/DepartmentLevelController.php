<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\DepartmentLevel;
use Illuminate\Http\Request;

class DepartmentLevelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'rank' => 'required|integer|min:1',
        ]);

        DepartmentLevel::create($request->all());

        return redirect()->back()->with('message', 'Level created successfully.');
    }

    public function update(Request $request, DepartmentLevel $level)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rank' => 'required|integer|min:1',
        ]);

        $level->update($request->all());

        return redirect()->back()->with('message', 'Level updated successfully.');
    }

    public function destroy(DepartmentLevel $level)
    {
        $level->delete();
        return redirect()->back()->with('message', 'Level deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'level_ids' => 'required|array',
            'level_ids.*' => 'exists:department_levels,id',
        ]);

        foreach ($request->level_ids as $index => $id) {
            DepartmentLevel::where('id', $id)->update(['rank' => $index + 1]);
        }

        return redirect()->back()->with('message', 'Levels reordered successfully.');
    }
}
