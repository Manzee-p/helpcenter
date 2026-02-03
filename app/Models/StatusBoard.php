<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusBoard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'incident_number',
        'title',
        'description',
        'category',
        'affected_area',
        'status',
        'severity',
        'started_at',
        'resolved_at',
        'created_by',
        'assigned_to',
        'is_public',
        'is_pinned',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_public' => 'boolean',
        'is_pinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function updates()
    {
        return $this->hasMany(StatusUpdate::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['investigating', 'identified', 'monitoring']);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    // Helpers
    public function getStatusLabelAttribute()
    {
        $labels = [
            'investigating' => 'Sedang Diselidiki',
            'identified' => 'Masalah Teridentifikasi',
            'monitoring' => 'Dalam Pemantauan',
            'resolved' => 'Selesai',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getSeverityLabelAttribute()
    {
        $labels = [
            'critical' => 'Kritis',
            'high' => 'Tinggi',
            'medium' => 'Sedang',
            'low' => 'Rendah',
        ];
        return $labels[$this->severity] ?? $this->severity;
    }

    public function getCategoryLabelAttribute()
    {
        $labels = [
            'power_outage' => 'Gangguan Listrik',
            'technical_issue' => 'Masalah Teknis',
            'facility_issue' => 'Masalah Fasilitas',
            'network_issue' => 'Gangguan Jaringan',
            'other' => 'Lainnya',
        ];
        return $labels[$this->category] ?? $this->category;
    }
}