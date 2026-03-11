<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use Illuminate\Http\Request;

class TicketTypeController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Configuration/TicketTypes/Index', [
            'ticketTypes' => TicketType::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_types,name',
            'icon' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:255',
            'severity_factor' => 'required|numeric|min:1',
            'is_confidential' => 'boolean',
            'allow_anonymous' => 'boolean',
        ]);

        TicketType::create($validated);

        return back()->with('message', 'Ticket type created successfully.');
    }

    public function destroy(TicketType $ticketType)
    {
        $ticketType->delete();
        return back()->with('message', 'Ticket type deleted successfully.');
    }
}
