<?php

namespace App\Models;

use App\Models\Delivery;
use App\Models\DeliveryCheckpoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeliveryPhoto extends Model
{
    protected $fillable = [
        'delivery_id',
        'checkpoint_id',
        'photo_path',
        'photo_type',
        'latitude',
        'drive_file_id',
        'longitude',
        'captured_at',
        'caption'
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'captured_at' => 'datetime',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function checkpoint()
    {
        return $this->belongsTo(DeliveryCheckpoint::class, 'checkpoint_id');
    }

    // ==================================
    // GET PHOTO
    // ==================================
    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->drive_file_id) {
            return "https://lh3.googleusercontent.com/d/{$this->drive_file_id}";
        }

        // Fallback untuk foto lama yang belum punya drive_file_id
        Log::warning('DeliveryPhoto: drive_file_id kosong', [
            'photo_id' => $this->id,
            'path'     => $this->photo_path,
        ]);

        return null;
    }

    public function getPhotoAttribute()
    {
        return $this->photo_url;
    }

    public function getPhotoTypeBadgeAttribute()
    {
        $badges = [
            'pickup' => '📦 Pickup',
            'delivery' => '🚚 Delivery',
            'proof' => '✅ Bukti',
            'damage' => '⚠ Kerusakan'
        ];

        return $badges[$this->photo_type] ?? '📷';
    }

    public function scopeOfCheckpoint($query, $checkpointId)
    {
        return $query->where('checkpoint_id', $checkpointId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('photo_type', $type);
    }
}
