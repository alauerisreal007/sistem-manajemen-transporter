<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Delivery;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status'
    ];

    /**
     * Check whether user has a role (accepts string, pipe-delimited string, or array)
     */
    public function hasRole(string $roles): bool
    {
        return $this->role === $roles;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPERADMIN);
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function hasAdminAccess(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ADMIN]);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Convenience check for admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isUser(): bool
    {
        return $this->hasRole(self::ROLE_USER);
    }

    public function createApiToken(): string
    {
        $this->tokens()->delete();
        
        $token = $this->createToken('api-token' . $this->id);

        return $token->plainTextToken;
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }

    public function activeRoutes()
    {
        return $this->hasMany(Route::class, 'driver_id')->where('status', 'active');
    }

    public function completedRoutes()
    {
        return $this->hasMany(Route::class, 'driver_id')->where('status', 'completed');
    }

    public function hasActiveDelivery()
    {
        return $this->deliveries()->whereIn('status', ['pending', 'in_progress'])->exists();
    }

    public function hasActiveRoutes(): bool
    {
        return $this->hasActiveDelivery();
    }

    public function hasCompletedRoutes(): int
    {
        return $this->completedRoutes()->count();
    }

    public function scopeActiveDrivers($query)
    {
        return $query->where('role', self::ROLE_USER)->where('status', 'active');
    }

    public function scopeAvailableDrivers($query)
    {
        return $query->where('role', self::ROLE_USER)->where('status', 'active')->whereDoesntHave('deliveries', function ($q) {
            $q->whereIn('status', ['pending', 'in_progress']);
        });
    }

    public static function totalDrivers()
    {
        return static::where('role', 'user')->count();
    }

    public static function topDriversByDate($date)
    {
        return static::where('role', 'user')
                ->withCount([
                    'deliveries as completed_today' => function ($q) use ($date) {
                        $q->where('status', 'completed')
                            ->whereDate('completed_at', $date);
                    }
                ])
                ->having('completed_today', '>', 0)
                ->orderByDesc('completed_today')
                ->limit(5)
                ->get();
    }

    public static function topDrivers()
    {
        return static::where('role', 'user')
                ->withCount([
                    'deliveries as completed_today' => function($q) {
                        $q->where('status', 'completed')
                            ->whereDate('completed_at', today());
                    }
                ])
                ->having('completed_today', '>', 0)
                ->orderByDesc('completed_today')
                ->limit(5)
                ->get();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
