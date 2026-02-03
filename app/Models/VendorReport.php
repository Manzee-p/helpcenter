<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'period_type',
        'period_start',
        'period_end',
        'total_tickets',
        'resolved_tickets',
        'pending_tickets',
        'avg_response_time',
        'avg_resolution_time',
        'sla_compliance_rate',
        'tickets_by_category',
        'tickets_by_priority',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'tickets_by_category' => 'array',
        'tickets_by_priority' => 'array',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
