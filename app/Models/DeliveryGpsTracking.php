<?php

namespace App\Models;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryGpsTracking extends Model
{
    use HasFactory;
    protected $fillable = [
        'delivery_id',
        'driver_id',
        'latitude',
        'longitude',
        'speed',
        'accuracy',
        'heading',
        'battery_level',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'recorded_at' => 'datetime'
    ];

    // =================================
    // RELATIONS
    // =================================
    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // =================================
    // GET LAST LOCATION
    // =================================
    public function getLastLocation($deliveryId)
    {
        return static::where('delivery_id', $deliveryId)
                ->latest('recorded_at')
                ->first();
    }

    // =================================
    // GET HISTORY
    // =================================
    public function getHistory($deliveryId, $minutes = 60)
    {
        return static::where('delivery_id', $deliveryId)
                ->where('recorded_at', '>=', now()->subMinutes($minutes))
                ->orderBy('recorded_at', 'desc')
                ->get();
    }
}
