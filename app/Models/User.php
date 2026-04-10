<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\TicketDeletionRequest;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'company',
        'department',
        'emergency_contact',
        'emergency_contact_name',
        'emergency_contact_relation',
        'address',
        'city',
        'province',
        'postal_code',
        'gender',
        'birth_date',
        'nik',
        'password',
        'role',
        'bio',
        'avatar',
        'is_active',
        'company_name',
        'company_address',
        'company_phone',
        'specialization',
        'email_verified_at',
        'two_factor_enabled',
        'preferences',
        'notification_settings',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date:Y-m-d', // ✅ FORMAT YANG BENAR!
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'preferences' => 'array',
            'notification_settings' => 'array',
        ];
    }

    protected $appends = ['avatar_url', 'initials', 'age'];

    // ✅ FIXED: Safe birth_date accessor
    public function getBirthDateAttribute($value)
    {
        if (!$value) return null;
        
        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }
        
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return $value;
        }
    }

    // ✅ Mutator untuk birth_date
    public function setBirthDateAttribute($value)
    {
        if (!$value) {
            $this->attributes['birth_date'] = null;
            return;
        }
        
        try {
            $this->attributes['birth_date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->attributes['birth_date'] = null;
        }
    }

    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    public function getInitialsAttribute()
    {
        $nameParts = explode(' ', trim($this->name));
        
        if (count($nameParts) >= 2) {
            return strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
        }
        
        return strtoupper(substr($this->name, 0, 2));
    }

   // ✅ FIXED: Safe age accessor with null check
    public function getAgeAttribute()
    {
        // ✅ Check if birth_date exists and is not null
        if (!isset($this->attributes['birth_date']) || !$this->attributes['birth_date']) {
            return null;
        }
        
        try {
            return Carbon::parse($this->attributes['birth_date'])->age;
        } catch (\Exception $e) {
            Log::warning('Failed to calculate age', [
                'user_id' => $this->id ?? 'unknown',
                'birth_date' => $this->attributes['birth_date'] ?? 'not set',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    // ✅ FIXED: Safe full address accessor
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->attributes['address'] ?? null,
            $this->attributes['city'] ?? null,
            $this->attributes['province'] ?? null,
            $this->attributes['postal_code'] ?? null
        ]);
        
        return implode(', ', $parts) ?: null;
    }

    // ✅ FIXED: Safe gender label accessor
    public function getGenderLabelAttribute()
    {
        if (!isset($this->attributes['gender'])) {
            return null;
        }
        
        return match($this->attributes['gender']) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => null
        };
    }

    // ✅ FIXED: Safe emergency contact accessor
    public function getEmergencyContactInfoAttribute()
    {
        if (!isset($this->attributes['emergency_contact']) || !$this->attributes['emergency_contact']) {
            return null;
        }
        
        return [
            'phone' => $this->attributes['emergency_contact'],
            'name' => $this->attributes['emergency_contact_name'] ?? null,
            'relation' => $this->attributes['emergency_contact_relation'] ?? null
        ];
    }

    // RELATIONSHIPS
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function vendorReports()
    {
        return $this->hasMany(VendorReport::class, 'vendor_id');
    }

    public function vendorWarnings()
    {
        return $this->hasMany(VendorWarning::class, 'vendor_id');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->where('is_read', false);
    }

    public function loginHistory()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function ticketDeletionRequests()
    {
        return $this->hasMany(TicketDeletionRequest::class, 'user_id');
    }

    // HELPER METHODS
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isVendor()
    {
        return $this->role === 'vendor';
    }

    public function isClient()
    {
        return $this->role === 'client';
    }

    public function hasCompleteProfile()
    {
        return !empty($this->name) 
            && !empty($this->email) 
            && !empty($this->phone)
            && !empty($this->address)
            && !empty($this->city);
    }

    public function getProfileCompletion()
    {
        $fields = [
            'name', 'email', 'phone', 
            'emergency_contact', 'emergency_contact_name', 'emergency_contact_relation',
            'address', 'city', 'province', 'postal_code',
            'gender', 'birth_date', 'nik', 'bio', 'avatar'
        ];
        
        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }
        
        return round(($completed / count($fields)) * 100);
    }

    public function hasEmergencyContact()
    {
        return !empty($this->emergency_contact);
    }
}
