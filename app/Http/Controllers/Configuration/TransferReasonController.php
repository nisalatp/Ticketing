<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\TransferReason;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransferReasonController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuration/TransferReasons/Index', [
            'reasons' => TransferReason::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);
        
        TransferReason::create($request->all());
        
        return redirect()->back()->with('message', 'Transfer Reason created successfully.');
    }

    public function update(Request $request, TransferReason $transferReason)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);
        
        $transferReason->update($request->all());
        
        return redirect()->back()->with('message', 'Transfer Reason updated successfully.');
    }

    public function destroy(TransferReason $transferReason)
    {
        $transferReason->delete();
        
        return redirect()->back()->with('message', 'Transfer Reason deleted successfully.');
    }
}
