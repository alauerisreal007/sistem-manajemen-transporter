<?php

namespace App\Models;

use App\Models\DeliveryCheckpoint;
use App\Models\DeliveryGpsTracking;
use App\Models\DeliveryPhoto;
use App\Models\Location;
use App\Models\Route;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'delivery_code',
        'route_id',
        'driver_id',
        'status',
        'started_at',
        'completed_at',
        'total_duration_minutes',
        'current_location_id',
        'current_sequence',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'notes',
        'cancellation_reason',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_location_update' => 'datetime',
        'current_latitude' => 'decimal:7',
        'current_longitude' => 'decimal:7'
    ];

    // ======================================
    // RELATIONSHIPS
    // ======================================

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function currentLocation()
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function checkpoints()
    {
        return $this->hasMany(DeliveryCheckpoint::class)->orderBy('sequence');
    }

    public function gpsTracking()
    {
        return $this->hasMany(DeliveryGpsTracking::class)->orderBy('recorded_at', 'desc');
    }

    public function photos()
    {
        return $this->hasMany(DeliveryPhoto::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==========================================
    // SCOPE STATUS
    // ==========================================
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // =========================================================
    // ACCESSORS
    // =========================================================
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">⏳ Menunggu</span>',
            'in_progress' => '<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">🚚 Dalam Perjalanan</span>',
            'completed' => '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">✅ Selesai</span>',
            'cancelled' => '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">❌ Dibatalkan</span>'
        ];

        return $badges[$this->status] ?? '-';
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->total_duration_minutes) {
            return '-';
        }

        $hours = floor($this->total_duration_minutes / 60);
        $minutes = $this->total_duration_minutes % 60;

        return $hours > 0
            ? "{$hours}j {$minutes}m"
            : "{$minutes} menit";
    }

    public function getAverageLoadDurationMinutes()
    {
        $totalDuration = $this->checkpoints
                            ->where('status', 'completed')
                            ->sum('load_duration_minutes');

        $count = $this->checkpoints->where('status', 'completed')->count();

        return $count > 0 ? $totalDuration / $count : 0;
    }

    public function getFormattedAverageDurationAttribute()
    {
        $avgDuration = $this->getAverageLoadDurationMinutes();

        if ($avgDuration == 0) {
            return '-';
        }

        $hours = floor($avgDuration / 60);
        $minutes = round($avgDuration % 60);

        return ($hours > 0 ? $hours . 'j ' : '') . $minutes . 'm';
    }

    public function getProgressPercentageAttribute()
    {
        if (!$this->checkpoints_count) {
            return 0;
        }

        return round(
            ($this->completed_checkpoints_count / $this->checkpoints_count) * 100
        );
    }

    public function getTotalDeliveryLocationsAttribute()
    {
        return $this->route->deliveryLocations->count();
    }
    
    // ==========================================
    // BUSINESS LOGIC
    // ==========================================
    /**
     * Menghitung rata-rata durasi delivery yang completed
     * @param Collection $deliveriesCollection
     * @return string Formatted duration (e.g. "2j 30m" atau "-")
     */
    public static function calculateAverageCompletedDuration($deliveriesCollection)
    {
        $completedDeliveries = $deliveriesCollection->where('status', 'completed');
        
        if ($completedDeliveries->count() === 0) {
            return '-';
        }
        
        $totalDuration = $completedDeliveries->sum(function ($delivery) {
            return $delivery->checkpoints
                        ->where('status', 'completed')
                        ->sum('load_duration_minutes');
        });
        
        $avgDuration = $totalDuration / $completedDeliveries->count();
        $hours = floor($avgDuration / 60);
        $minutes = round($avgDuration % 60);
        
        return ($hours > 0 ? $hours . 'j ' : '') . $minutes . 'm';
    }

    public static function countActiveDeliveries()
    {
        return static::inProgress()->count();
    }

    public static function completedCount($date)
    {
        return static::completed()->whereDate('completed_at', $date)->count();
    }

    public static function countCompletedToday()
    {
        return static::completed()->whereDate('completed_at', today())->count();
    }

    public static function durationStatsToday()
    {
        return static::completed()
                    ->whereDate('completed_at', today())
                    ->selectRaw('
                        AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_duration,
                        MIN(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as best_duration,
                        MAX(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as worst_duration
                    ')
                    ->first();
    }

    // Duration stats berdasarkan tanggal apapun
    public static function durationStatsByDate($date)
    {
        return static::completed()
                    ->whereDate('completed_at', $date)
                    ->selectRaw('
                        AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_duration,
                        MIN(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as best_duration,
                        MAX(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as worst_duration
                    ')
                    ->first();
    }

    // On-time rate berdasarkan tanggal apapun
    public static function onTimeRateByDate($date)
    {
        $completed = static::completedCount($date);

        if ($completed === 0) return 0;

        $onTime = static::completed()
                    ->whereDate('completed_at', $date)
                    ->whereNotNull('completed_at')
                    ->count();

        return round(($onTime / $completed) * 100, 1);
    }

    public static function onTimeRateToday()
    {
        $completed = static::countCompletedToday();

        if ($completed === 0) return 0;

        $onTime = static::completed()->whereDate('completed_at', today())->whereNotNull('completed_at')->count();

        return round(($onTime / $completed) * 100, 1); 
    }

    public static function totalAverageDuration($date)
    {
        return static::completed()->whereDate('completed_at', $date)->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg')->value('avg');
    }

    public static function activeDriversCount()
    {
        return static::inProgress()->distinct('driver_id')->count('driver_id');
    }

    // Duration by route berdasarkan 7 hari sebelum tanggal dipilih
    public static function durationByRouteByDate($date)
    {
        return static::with('route')
                ->completed()
                ->whereDate('completed_at', '>=', Carbon::parse($date)->subDays(7))
                ->whereDate('completed_at', '<=', $date)
                ->select(
                    'route_id',
                    DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_duration'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('route_id')
                ->orderByDesc('avg_duration')
                ->limit(10)
                ->get();
    }

    public static function durationByRouteLast7Days()
    {
        return static::with('route')
                            ->completed()
                            ->whereDate('completed_at', '>=', today()->subDays(7))
                            ->select('route_id', DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_duration'), DB::raw('COUNT(*) as count'))
                            ->groupBy('route_id')
                            ->orderByDesc('avg_duration')
                            ->limit(10)
                            ->get();
    }

    public static function activeDeliveriesList()
    {
        return static::with(['driver', 'route', 'checkpoints'])
                        ->inProgress()
                        ->latest('started_at')
                        ->limit(5)
                        ->get();
    }

    public static function pendingDeliveriesList()
    {
        return static::with(['route', 'driver'])
                    ->pending()
                    ->latest()
                    ->limit(5)
                    ->get();
    }
    
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canStart()
    {
        return $this->status === 'pending';
    }

    public function canCancel()
    {
        return in_array($this->status, ['pending', 'in_progress']);
    }

    // Start delivery journey
    public function start()
    {
        if (!$this->canStart()) {
            throw new \Exception('Delivery cannot be started in current status');
        }

        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'current_sequence' => 0
        ]);

        // Tandai checkpoint pertama (pickup) sebagai in progress
        $firstCheckpoint = $this->checkpoints()->where('sequence', 0)->first();
        if ($firstCheckpoint) {
            $firstCheckpoint->update(['status' => 'in_progress']);
        }

        return $this;
    }

    // Complete delivery
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'total_duration_minutes' => $this->started_at ? $this->started_at->diffInMinutes(now()) : null,
        ]);

        return $this;
    }

    public function cancel($reason = null)
    {
        if (!$this->canCancel()) {
            throw new \Exception('Deliveries cannot be cancelled in current status');
        }

        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason
        ]);

        return $this;
    }

    public function updateLocation($latitude, $longitude, $additionalData = [])
    {
        // Update delivery current location
        $this->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'last_location_update' => now()
        ]);

        // Store in GPS history
        $this->gpsTracking()->create(array_merge([
            'driver_id' => $this->driver_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'recorded_at' => now()
        ], $additionalData));

        return $this;
    }

    public function getCurrentCheckpoint()
    {
        return $this->checkpoints()
                ->where('sequence', $this->current_sequence)
                ->first();
    }

    public function getNextCheckpoint()
    {
        return $this->checkpoints()
                ->where('sequence', '>', $this->current_sequence)
                ->where('status', 'pending')
                ->orderBy('sequence')
                ->first();
    }

    // public function getNextCheckpoints

    public function getNextCheckpoints()
    {
        $nextCheckpoint = $this->getNextCheckpoint();

        if (!$nextCheckpoint) {
            // No more checkpoints, complete delivery
            $this->complete();
            return null;
        }

        $this->update([
            'current_sequence' => $nextCheckpoint->sequence,
            'current_location_id' => $nextCheckpoint->location_id
        ]);

        $nextCheckpoint->update(['status' => 'in_progress']);
        
        return $nextCheckpoint;
    }

    public static function generateDeliveryCode()
    {
        $date = now()->format('Ymd');
        $lastDelivery = static::whereDate('created_at', today())
                ->latest('id')
                ->first();

        $sequence = $lastDelivery ? intval(substr($lastDelivery->delivery_code, -4)) + 1 : 1;

        return sprintf('DEL-%s-%04d', $date, $sequence);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($delivery) {
            if (!$delivery->delivery_code) {
                $delivery->delivery_code = static::generateDeliveryCode();
            }
        });
    }
}
