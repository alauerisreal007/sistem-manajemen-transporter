<?php

namespace App\Models;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = 'routes';
    protected $fillable = [
        'route_name',
        'pickup_location_id',
        'delivery_location_id',
        'distance_km',
        'estimated_time',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'assigned_at' => 'datetime'
    ];

    public function pickupLocation()
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    // Dikembalikan ke single delivery
    public function deliveryLocation()
    {
        return $this->belongsTo(Location::class, 'delivery_location_id');
    }

    // Multiple delivery locations
    public function deliveryLocations()
    {
        return $this->belongsToMany(Location::class, 'route_delivery_locations', 'route_id', 'location_id')
            ->withPivot('sequence')
            ->withTimestamps()
            ->orderBy('sequence');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function isAssigned(): bool
    {
        return !is_null($this->driver_id);
    }

    public function hasActiveDeliveries()
    {
        return $this->deliveries()->whereIn('status', ['in_progress', 'pending'])->exists();
    }

    public function hasMultipleDeliveries(): bool
    {
        return $this->deliveryLocation()->count() > 0;
    }

    public function getTotalDeliveryPointsAttribute()
    {
        return $this->deliveryLocations()->count();
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="bg-blue-100 text-blue-600 px-2 py-1 rounded-full text-xs font-semibold">Aktif</span>',
            'inactive' => '<span class="bg-red-100 text-red-600 px-2 py-1 rounded-full text-xs font-semibold">Tidak Aktif</span>',
            'completed' => '<span class="bg-green-100 text-green-600 px-2 py-1 rounded-full text-xs font-semibold">Selesai</span>'
        ];

        return $badges[$this->status] ?? '-';
    }

    public function getDeliveryListAttribute(): string
    {
        if ($this->deliveryLocations->count() > 0) {
            return $this->deliveryLocations->pluck('name')->join(', ');
        }

        return $this->deliveryLocation ? $this->deliveryLocation->name : '-';
    }

    public function getFormattedDurationAttribute()
    {
        $totalMinutes = $this->estimated_time;
        if ($totalMinutes < 60) {
            return "{$totalMinutes}m";
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        if ($minutes === 0) {
            return "{$hours}j";
        }
        
        return "{$hours}j {$minutes}m";
    }

    public function getFormattedDistanceAttribute()
    {
        return number_format($this->distance_km, 1) . ' km';
    }
}
