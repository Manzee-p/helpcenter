<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'response_time_sla',
        'resolution_time_sla',
        'actual_response_time',
        'actual_resolution_time',
        'response_sla_met',
        'resolution_sla_met',
    ];

    protected $casts = [
        'response_sla_met' => 'boolean',
        'resolution_sla_met' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
