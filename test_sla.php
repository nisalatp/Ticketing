<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketType;
use App\Models\SlaPolicy;

// Ensure a user exists
$user = User::first();
if (!$user) {
    echo "No users found. Cannot run test.\n";
    exit;
}

// 1. Create a Department
$dept = Department::firstOrCreate(['name' => 'IT Support']);

// 2. Create an SLA Policy for IT Support / Urgent Priority
$urgentPolicy = SlaPolicy::firstOrCreate(
    ['name' => 'IT Urgent SLA', 'department_id' => $dept->id, 'priority' => 'Urgent'],
    ['response_minutes' => 15, 'resolution_minutes' => 60, 'is_active' => true]
);

// 3. Create a Global SLA Policy for Low Priority
$globalLowPolicy = SlaPolicy::firstOrCreate(
    ['name' => 'Global Low SLA', 'department_id' => null, 'priority' => 'Low'],
    ['response_minutes' => 1440, 'resolution_minutes' => 2880, 'is_active' => true]
);

$category = TicketCategory::firstOrCreate(['name' => 'General', 'department_id' => $dept->id]);

// Let's create a ticket type to test severity factor
$ticketType = TicketType::firstOrCreate(
    ['name' => 'Critical Incident', 'category_id' => $category->id],
    ['icon' => 'FireIcon', 'color_code' => 'text-red-500', 'severity_factor' => 2.0]
);

use App\Models\Queue;
$queue = Queue::firstOrCreate(['name' => 'General Queue', 'department_id' => $dept->id]);

// Create an Urgent Ticket
$urgentTicket = Ticket::create([
    'ticket_no' => 'TKT-URG-' . rand(1000, 9999),
    'requester_user_id' => $user->id,
    'department_id' => $dept->id,
    'queue_id' => $queue->id,
    'category_id' => $category->id,
    'type_id' => $ticketType->id, // Severity 2.0 * Urgent Base Weight 5.0 = 10.0
    'priority' => 'Urgent',
    'status' => 'New',
    'subject' => 'Server Down',
    'description' => 'The main server is down.',
]);

// Create a Low Ticket
$lowTicket = Ticket::create([
    'ticket_no' => 'TKT-LOW-' . rand(1000, 9999),
    'requester_user_id' => $user->id,
    'department_id' => $dept->id,
    'queue_id' => $queue->id,
    'category_id' => $category->id,
    'type_id' => $ticketType->id, // Use valid type_id
    'priority' => 'Low',
    'status' => 'New',
    'subject' => 'Mouse not working',
    'description' => 'Need a new mouse.',
]);

echo "--- SLA & Weight Calculations ---\n";
echo "Urgent Ticket [TKT-URG] Weight (Expected 10.0): " . $urgentTicket->weight . "\n";
echo "Urgent Ticket [TKT-URG] SLA Breach (Expected +60 mins): " . $urgentTicket->sla_breach_at . "\n";
echo "Low Ticket [TKT-LOW] Weight (Expected 1.0): " . $lowTicket->weight . "\n";
echo "Low Ticket [TKT-LOW] SLA Breach (Expected +2880 mins / 48 hrs): " . $lowTicket->sla_breach_at . "\n";
