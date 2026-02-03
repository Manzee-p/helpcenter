<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_board_id',
        'user_id',
        'message',
        'update_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function statusBoard()
    {
        return $this->belongsTo(StatusBoard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}