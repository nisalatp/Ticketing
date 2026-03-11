<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Configuration/Departments/Index', [
            'departments' => Department::with(['levels', 'topics'])->orderBy('name')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'is_shared_service' => 'boolean',
        ]);

        if ($request->is_shared_service) {
            // Ensure only one shared service exists
            if (Department::where('is_shared_service', true)->exists()) {
                return redirect()->back()->withErrors(['is_shared_service' => 'A Management Services department already exists. Only one is allowed.']);
            }
        }

        Department::create($request->only('name', 'is_shared_service'));

        return redirect()->back()->with('message', 'Department created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'is_shared_service' => 'boolean',
        ]);

        $department->update($request->only('name', 'is_shared_service'));

        return redirect()->back()->with('message', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        try {
            // Check for dependencies before deleting
            if ($department->users()->exists() || $department->tickets()->exists()) {
                return redirect()->back()->withErrors(['error' => 'Cannot delete department with assigned users or tickets.']);
            }

            $department->delete();
            return redirect()->back()->with('message', 'Department deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete department due to related records (e.g., SLAs or Topics).']);
        }
    }
}
