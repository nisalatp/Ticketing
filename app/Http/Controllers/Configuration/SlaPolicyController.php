<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SlaPolicy;
use App\Models\Department;

class SlaPolicyController extends Controller
{
    public function index()
    {
        $policies = SlaPolicy::with(['scopes.department', 'scopes.topic'])->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $topics = \App\Models\Topic::orderBy('name')->get();

        return Inertia::render('Configuration/SlaPolicies/Index', [
            'policies' => $policies,
            'departments' => $departments,
            'topics' => $topics,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'response_minutes' => 'required|integer|min:0',
            'resolution_minutes' => 'required|integer|min:0|gte:response_minutes',
            'is_active' => 'boolean',
            'scopes' => 'required|array|min:1',
            'scopes.*.department_id' => 'nullable|exists:departments,id',
            'scopes.*.topic_ids' => 'nullable|array',
            'scopes.*.topic_ids.*' => 'exists:topics,id',
            'scopes.*.priority' => 'nullable|in:Low,Medium,High,Urgent',
        ]);

        $policy = SlaPolicy::create([
            'name' => $validated['name'],
            'response_minutes' => $validated['response_minutes'],
            'resolution_minutes' => $validated['resolution_minutes'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        foreach ($validated['scopes'] as $scope) {
            if (!empty($scope['topic_ids'])) {
                foreach ($scope['topic_ids'] as $topicId) {
                    $policy->scopes()->create([
                        'department_id' => $scope['department_id'] ?? null,
                        'topic_id' => $topicId,
                        'priority' => $scope['priority'] ?? null,
                    ]);
                }
            } else {
                $policy->scopes()->create([
                    'department_id' => $scope['department_id'] ?? null,
                    'topic_id' => null,
                    'priority' => $scope['priority'] ?? null,
                ]);
            }
        }

        return redirect()->back()->with('message', 'SLA Policy created successfully.');
    }

    public function update(Request $request, SlaPolicy $sla_policy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'response_minutes' => 'required|integer|min:0',
            'resolution_minutes' => 'required|integer|min:0|gte:response_minutes',
            'is_active' => 'boolean',
            'scopes' => 'required|array|min:1',
            'scopes.*.department_id' => 'nullable|exists:departments,id',
            'scopes.*.topic_ids' => 'nullable|array',
            'scopes.*.topic_ids.*' => 'exists:topics,id',
            'scopes.*.priority' => 'nullable|in:Low,Medium,High,Urgent',
        ]);

        $sla_policy->update([
            'name' => $validated['name'],
            'response_minutes' => $validated['response_minutes'],
            'resolution_minutes' => $validated['resolution_minutes'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $sla_policy->scopes()->delete();
        foreach ($validated['scopes'] as $scope) {
            if (!empty($scope['topic_ids'])) {
                foreach ($scope['topic_ids'] as $topicId) {
                    $sla_policy->scopes()->create([
                        'department_id' => $scope['department_id'] ?? null,
                        'topic_id' => $topicId,
                        'priority' => $scope['priority'] ?? null,
                    ]);
                }
            } else {
                $sla_policy->scopes()->create([
                    'department_id' => $scope['department_id'] ?? null,
                    'topic_id' => null,
                    'priority' => $scope['priority'] ?? null,
                ]);
            }
        }

        return redirect()->back()->with('message', 'SLA Policy updated successfully.');
    }

    public function destroy(SlaPolicy $sla_policy)
    {
        $sla_policy->delete();

        return redirect()->back()->with('message', 'SLA Policy deleted successfully.');
    }
}
