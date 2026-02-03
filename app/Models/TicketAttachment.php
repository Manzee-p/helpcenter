<?php

// FILE: app/Models/TicketAttachment.php
// Pastikan model ini ada dan benar

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // Relationship
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Helper method to get full URL
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}