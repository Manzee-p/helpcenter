<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketAdditionalInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'note',
        'photo_path',
        'photo_name',
        'photo_type',
        'photo_size',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

