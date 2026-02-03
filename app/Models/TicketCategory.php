<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TicketCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method untuk auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate slug saat creating
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
                
                // Handle duplicate slug
                $originalSlug = $category->slug;
                $count = 1;
                
                while (static::where('slug', $category->slug)->exists()) {
                    $category->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
        
        // Auto-update slug saat name berubah
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
                
                // Handle duplicate slug (exclude current record)
                $originalSlug = $category->slug;
                $count = 1;
                
                while (static::where('slug', $category->slug)
                    ->where('id', '!=', $category->id)
                    ->exists()) {
                    $category->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }
}