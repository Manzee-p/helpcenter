<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketDeletionRequest extends Model
{
    use HasFactory;

    public const REASONS = [
        'duplicate_ticket',
        'issue_resolved_without_action',
        'wrong_category_or_input',
        'ticket_created_by_mistake',
        'no_longer_relevant',
    ];

    protected $fillable = [
        'ticket_id',
        'user_id',
        'reasons',
        'custom_reason',
        'status',
        'reviewed_by',
        'admin_note',
        'reviewed_at',
    ];

    protected $casts = [
        'reasons' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
