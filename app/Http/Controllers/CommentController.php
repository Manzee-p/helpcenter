<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Get comments for a ticket
     */
    public function getComments($ticketId)
    {
        try {
            $ticket = Ticket::findOrFail($ticketId);
            
            // Check if user has access to this ticket
            $user = Auth::user();
            if ($user->role === 'client' && $ticket->user_id !== $user->id) {
                return response()->json([
                    'message' => 'You do not have access to this ticket'
                ], 403);
            }
            
            if ($user->role === 'vendor' && $ticket->assigned_to !== $user->id) {
                return response()->json([
                    'message' => 'You are not assigned to this ticket'
                ], 403);
            }
            
            // Get comments with user info
            $comments = Comment::with('user')
                ->where('ticket_id', $ticketId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Filter internal comments for clients
            if ($user->role === 'client') {
                $comments = $comments->filter(function($comment) {
                    return !$comment->is_internal;
                });
            }
            
            return response()->json($comments->values());
            
        } catch (\Exception $e) {
            Log::error('Error fetching comments: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch comments',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Add a comment to a ticket
     */
    public function addComment(Request $request, $ticketId)
    {
        try {
            $validated = $request->validate([
                'comment' => 'required|string',
                'is_internal' => 'sometimes|boolean'
            ]);
            
            $ticket = Ticket::findOrFail($ticketId);
            
            // Check if user has access to this ticket
            $user = Auth::user();
            if ($user->role === 'client' && $ticket->user_id !== $user->id) {
                return response()->json([
                    'message' => 'You do not have access to this ticket'
                ], 403);
            }
            
            if ($user->role === 'vendor' && $ticket->assigned_to !== $user->id) {
                return response()->json([
                    'message' => 'You are not assigned to this ticket'
                ], 403);
            }
            
            // Clients cannot create internal comments
            if ($user->role === 'client') {
                $validated['is_internal'] = false;
            }
            
            // Create comment
            $comment = Comment::create([
                'ticket_id' => $ticketId,
                'user_id' => $user->id,
                'comment' => $validated['comment'],
                'is_internal' => $validated['is_internal'] ?? false
            ]);
            
            // Load user relationship
            $comment->load('user');
            
            // Update ticket's first_response_at if this is the first staff response
            if (in_array($user->role, ['admin', 'vendor']) && !$ticket->first_response_at) {
                $ticket->first_response_at = now();
                $ticket->save();
                
                // Update SLA tracking
                if ($ticket->slaTracking) {
                    $sla = $ticket->slaTracking;
                    $actualResponseTime = (int) round(
                        $ticket->created_at->diffInMinutes($ticket->first_response_at)
                    );
                    $sla->actual_response_time = $actualResponseTime;
                    $sla->response_sla_met = $actualResponseTime <= $sla->response_time_sla;
                    $sla->save();
                }
            }
            
            // Send notification to relevant parties
            if ($user->role === 'client') {
                // Notify assigned vendor
                if ($ticket->assigned_to) {
                    NotificationController::createNotification(
                        $ticket->assigned_to,
                        'new_comment',
                        'New Comment on Ticket',
                        "Client added a comment on ticket: {$ticket->title}",
                        $ticket->id
                    );
                }
            } else {
                // Notify client (only if not internal)
                if (!$validated['is_internal']) {
                    NotificationController::createNotification(
                        $ticket->user_id,
                        'new_comment',
                        'New Response to Your Ticket',
                        "There's a new response on your ticket: {$ticket->title}",
                        $ticket->id
                    );
                }
            }
            
            return response()->json([
                'message' => 'Comment added successfully',
                'comment' => $comment
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to add comment',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}