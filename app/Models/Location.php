<?php

namespace App\Models;

use App\Models\Route;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $fillable = [
        'name',
        'type',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'city',
        'province',
        'phone',
        'notes',
        'status'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    public function pickupRoutes()
    {
        return $this->hasMany(Route::class, 'pickup_location_id');
    }

    public function deliveryRoutes()
    {
        return $this->hasMany(Route::class, 'delivery_location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLocationType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'active' => '<span class="bg-green-100 text-green-600 rounded-full px-2 py-1 text-xs font-bold">Aktif</span>',
            'inactive' => '<span class="bg-red-100 text-red-600 rounded-full px-2 py-1 text-xs font-bold">Tidak Aktif</span>'
        };
    }

    public function getTypeBadgeAttribute()
    {
        return match ($this->type) {
            'pickup' => '<span class="bg-blue-100 text-blue-700 text-xs font-semibold rounded-full px-2 py-1">Pickup</span>',
            'delivery' => '<span class="bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full px-2 py-1">Delivery</span>'
        };
    }
}
