<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Ticket;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $vendors = User::where('role', 'vendor')->pluck('id')->toArray();
        $clients = User::where('role', 'client')->pluck('id')->toArray();

        if (empty($vendors) || empty($clients)) {
            $this->command->error('Please run UserSeeder first!');
            return;
        }

        $tickets = Ticket::inRandomOrder()->take(20)->get();

        $notificationTypes = [
            'vendor' => [
                [
                    'type' => 'ticket_assigned',
                    'title' => 'New Ticket Assigned',
                    'message' => 'A new ticket has been assigned to you: {title}'
                ],
                [
                    'type' => 'comment_added',
                    'title' => 'New Comment on Ticket',
                    'message' => 'Client added a comment on ticket: {title}'
                ],
                [
                    'type' => 'ticket_updated',
                    'title' => 'Ticket Updated',
                    'message' => 'Ticket priority changed to {priority}: {title}'
                ],
            ],
            'client' => [
                [
                    'type' => 'ticket_status_changed',
                    'title' => 'Ticket Status Updated',
                    'message' => 'Your ticket status changed to {status}: {title}'
                ],
                [
                    'type' => 'ticket_assigned',
                    'title' => 'Ticket Assigned',
                    'message' => 'Your ticket has been assigned to a vendor: {title}'
                ],
                [
                    'type' => 'ticket_resolved',
                    'title' => 'Ticket Resolved',
                    'message' => 'Your ticket has been resolved: {title}'
                ],
            ]
        ];

        $count = 0;

        // Create notifications for vendors
        foreach ($vendors as $vendorId) {
            $vendorTickets = Ticket::where('assigned_to', $vendorId)
                ->inRandomOrder()
                ->take(5)
                ->get();
            
            foreach ($vendorTickets as $ticket) {
                $notifTemplate = $notificationTypes['vendor'][array_rand($notificationTypes['vendor'])];
                
                $message = str_replace(
                    ['{title}', '{priority}', '{status}'],
                    [$ticket->title, $ticket->priority, $ticket->status],
                    $notifTemplate['message']
                );

                Notification::create([
                    'user_id' => $vendorId,
                    'type' => $notifTemplate['type'],
                    'title' => $notifTemplate['title'],
                    'message' => $message,
                    'related_id' => $ticket->id,
                    'related_type' => 'ticket',
                    'read_at' => rand(0, 1) == 1 ? now() : null, // Random read status
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);

                $count++;
            }
        }

        // Create notifications for clients
        foreach ($clients as $clientId) {
            $clientTickets = Ticket::where('user_id', $clientId)
                ->inRandomOrder()
                ->take(3)
                ->get();
            
            foreach ($clientTickets as $ticket) {
                $notifTemplate = $notificationTypes['client'][array_rand($notificationTypes['client'])];
                
                $message = str_replace(
                    ['{title}', '{priority}', '{status}'],
                    [$ticket->title, $ticket->priority, $ticket->status],
                    $notifTemplate['message']
                );

                Notification::create([
                    'user_id' => $clientId,
                    'type' => $notifTemplate['type'],
                    'title' => $notifTemplate['title'],
                    'message' => $message,
                    'related_id' => $ticket->id,
                    'related_type' => 'ticket',
                    'read_at' => rand(0, 1) == 1 ? now() : null,
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);

                $count++;
            }
        }

        $this->command->info('✅ Created ' . $count . ' notifications');
    }
}