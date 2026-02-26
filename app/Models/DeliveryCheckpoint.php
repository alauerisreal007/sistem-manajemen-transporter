<?php

namespace App\Models;

use App\Models\Delivery;
use App\Models\DeliveryPhoto;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeliveryCheckpoint extends Model
{
    use HasFactory;
    protected $fillable = [
        'delivery_id',
        'location_id',
        'sequence',
        'type',
        'status',
        'arrived_at',
        'load_start_at',
        'load_end_at',
        'departed_at',
        'load_duration_minutes',
        'arrival_latitude',
        'arrival_longitude',
        'photos',
        'recipient_name',
        'recipient_signature_path',
        'signature_drive_file_id',
        'notes',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'load_start_at' => 'datetime',
        'load_end_at' => 'datetime',
        'departed_at' => 'datetime',
        'arrival_latitude' => 'decimal:7',
        'arrival_longitude' => 'decimal:7',
        'photos' => 'array'
    ];

    // =================================
    // RELATIONSHIPS
    // =================================
    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function checkpointPhotos()
    {
        return $this->hasMany(DeliveryPhoto::class, 'checkpoint_id');
    }

    // =================================
    // SCOPE
    // =================================

    public function scopePickup($query)
    {
        return $query->where('type', 'pickup');
    }

    public function scopeDelivery($query)
    {
        return $query->where('type', 'delivery');
    }

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

    // ========================================================
    // ACCESSORS
    // ========================================================
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '⏳ Menunggu',
            'in_progress' => '🚛 Sedang Proses',
            'completed' => '✅ Selesai',
            'skipped' => '⏭ Dilewati'
        ];

        return $badges[$this->status] ?? '-';
    }

    public function getFormattedLoadDurationAttribute()
    {
        if (!$this->load_duration_minutes) {
            return '-';
        }

        if ($this->load_duration_minutes < 60) {
            return "{$this->load_duration_minutes} menit";
        }

        $hours = floor($this->load_duration_minutes / 60);
        $minutes = $this->load_duration_minutes % 60;

        return "{$hours}j {$minutes}m";
    }

    public function getTypeIconAttribute()
    {
        return $this->type === 'pickup' ? '📦' : '🚚';
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if ($this->signature_drive_file_id) {
            return "https://lh3.googleusercontent.com/d/{$this->signature_drive_file_id}";
        }

        return null;
    }

    public function getSignatureAttribute()
    {
        return $this->signature_url;
    }

    // ===============================================
    // BUSINESS LOGIC
    // ===============================================

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

    public function isPickup()
    {
        return $this->type === 'pickup';
    }

    public function isDelivery()
    {
        return $this->type === 'delivery';
    }

    // Mark arrival at checkpoint
    public function markArrival($latitude = null, $longitude = null)
    {
        $this->update([
            'status' => 'in_progress',
            'arrived_at' => now(),
            'arrival_latitude' => $latitude,
            'arrival_longitude' => $longitude
        ]);

        return $this;
    }

    // Start loading/unloading
    public function startLoading()
    {
        if (!$this->arrived_at) {
            throw new \Exception('Must arrive a checkpoint first');
        }

        $this->update([
            'load_start_at' => now()
        ]);

        return $this;
    }

    public function endLoading()
    {
        if (!$this->load_start_at) {
            throw new \Exception('Loading must be started first');
        }

        $duration = $this->load_start_at->diffInMinutes(now());

        $this->update([
            'load_end_at' => now(),
            'load_duration_minutes' => $duration
        ]);
    }

    // Complete checkpoint and depart
    public function complete($recipientName = null, $signaturePath = null, $notes = null)
    {
        if ($this->load_start_at && !$this->load_end_at) {
            $this->endLoading();
        }

        $this->update([
            'status' => 'completed',
            'departed_at' => now(),
            'recipient_name' => $recipientName,
            'recipient_signature_path' => $signaturePath,
            'notes' => $notes
        ]);

        return $this;
    }

    // Add photo
    public function addPhoto(
        ?string $photoPath  = null,
        string  $type       = 'proof',
        ?float  $latitude   = null,
        ?float  $longitude  = null,
        ?string $caption    = null,
        ?string $driveFileId = null 
    ) {
        // Update kolom photos (array JSON, untuk backward compatibility)
        $photos   = $this->photos ?? [];
        $photos[] = $photoPath;
        $this->update(['photos' => $photos]);

        // Simpan record ke tabel delivery_photos
        $photo = $this->checkpointPhotos()->create([
            'delivery_id'   => $this->delivery_id,
            'photo_path'    => $photoPath,
            'drive_file_id' => $driveFileId, 
            'photo_type'    => $type,
            'latitude'      => $latitude,
            'longitude'     => $longitude,
            'caption'       => $caption,
            'captured_at'   => now()
        ]);

        return $photo;
    }

    // Get total time at checkpoint
    public function getTotalTimeMinutes()
    {
        if (!$this->arrived_at || !$this->departed_at) {
            return null;
        }

        return $this->arrived_at->diffInMinutes($this->departed_at);
    }

    public static function averageLoadTimeByDate($date)
    {
        return static::completed()
                ->whereDate('departed_at', $date)
                ->select(
                    DB::raw('AVG(CASE WHEN type = "pickup" THEN load_duration_minutes END) as avg_pickup'),
                    DB::raw('AVG(CASE WHEN type = "delivery" THEN load_duration_minutes END) as avg_delivery'),
                    DB::raw('AVG(load_duration_minutes) as avg_overall')
                )
                ->first();
    }

    // Average Load Time
    public static function averageLoadTime()
    {
        return static::completed()
                    ->whereDate('departed_at', today())
                    ->select(
                        DB::raw('AVG(CASE WHEN type = "pickup" THEN load_duration_minutes END) as avg_pickup'),
                        DB::raw('AVG(CASE WHEN type = "delivery" THEN load_duration_minutes END) as avg_delivery'),
                        DB::raw('AVG(load_duration_minutes) as avg_overall')
                    )
                    ->first();
    }

    // Last Week Average Load
    public static function lastWeekAverage()
    {
        return static::completed()
                ->whereBetween('departed_at', [
                    now()->subDays(14)->startOfDay(),
                    now()->subDays(7)->endOfDay()
                ])
                ->avg('load_duration_minutes');
    }

    public static function loadingByLocationByDate($date)
    {
        return static::with('location')
                    ->completed()
                    ->whereDate('departed_at', $date)
                    ->select('location',
                        DB::raw('AVG(load_duration_minutes) as avg_time'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->groupBy('location_id')
                    ->orderByDesc('avg_time')
                    ->limit(10)
                    ->get();
    }

    // Loading Time By Location
    public static function loadingByLocation()
    {
        return static::with('location')
                    ->completed()
                    ->whereDate('departed_at', today())
                    ->select('location_id', DB::raw('AVG(load_duration_minutes) as avg_time'), DB::raw('COUNT(*) as count'))
                    ->groupBy('location_id')
                    ->orderByDesc('avg_time')
                    ->limit(10)
                    ->get();
    }

    public static function averageLoadByDate($date)
    {
        return static::completed()
            ->whereDate('departed_at', $date)
            ->avg('load_duration_minutes');
    }
}
