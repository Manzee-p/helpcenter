<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'user_id',
        'created_by_admin', // NEW: Track admin who created on behalf of user
        'admin_notes', // NEW: Internal notes
        'category_id',
        'assigned_to',
        'status',
        'priority',
        'urgency_level',
        'event_name',
        'venue',
        'area',
        'assigned_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = 'TKT-' . date('Ymd') . '-' . str_pad(
                    Ticket::whereDate('created_at', today())->count() + 1, 
                    4, 
                    '0', 
                    STR_PAD_LEFT
                );
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(User::class, 'created_by_admin');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'ticket_id');
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }

    public function slaTracking()
    {
        return $this->hasOne(SlaTracking::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // NEW: Scope for filtering by creator type
    public function scopeCreatedByAdmin($query)
    {
        return $query->whereNotNull('created_by_admin');
    }

    public function scopeCreatedByUser($query)
    {
        return $query->whereNull('created_by_admin');
    }

    // Helper method
    public function isCreatedByAdmin()
    {
        return !is_null($this->created_by_admin);
    }
}