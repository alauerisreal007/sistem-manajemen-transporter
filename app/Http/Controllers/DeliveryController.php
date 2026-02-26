<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Route;
use App\Models\Delivery;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\DeliveryCheckpoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Delivery::with(['route.pickupLocation', 'route.deliveryLocations', 'driver', 'checkpoints.location']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by driver
        if ($request->has('driver_id') && $request->driver_id != '') {
            $query->where('driver_id', $request->driver_id);
        }

        // Search by delivery code or driver name
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('delivery_code', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('driver', function ($driverQuery) use ($searchTerm) {
                        $driverQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('route', function ($routeQuery) use ($searchTerm) {
                        $routeQuery->where('route_name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        $routes = Route::with(['pickupLocation', 'deliveryLocations'])
                        ->where('status', 'active')
                        ->get();

        $deliveries = $query->orderBy('created_at', 'desc')->paginate(10);

        $drivers = User::availableDrivers()->orderBy('name')->get();

        return view('delivery.index', compact('deliveries', 'routes', 'drivers'));
    }

    public function history(Request $request)
    {
        $driverId = Auth::id();
        $query = Delivery::query()
            ->with([
                'route:id,route_name',

                'checkpoints:id,delivery_id,sequence,status,type,location_id,' . 'arrived_at,departed_at,load_duration_minutes,recipient_name,' .
                'recipient_signature_path,signature_drive_file_id',

                'checkpoints.location:id,name,address',
                'checkpoints.checkpointPhotos:id,checkpoint_id,drive_file_id,photo_path',
            ])
            ->withCount([
                'checkpoints',
                'checkpoints as completed_checkpoints_count' => function ($q) {
                    $q->where('status', 'completed');
                }
            ])
            ->withAvg('gpsTracking as avg_speed', 'speed')
            ->withAvg('gpsTracking as avg_accuracy', 'accuracy')

            ->where('driver_id', $driverId)
            ->whereIn('status', ['completed', 'cancelled']);

        // Filter status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter tanggal
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $deliveries = $query->latest()->paginate(10)->withQueryString();

        // Summary statistik
        $summary = Delivery::selectRaw("
                COUNT(CASE WHEN status='completed' THEN 1 END) as total_completed,
                COUNT(CASE WHEN status='cancelled' THEN 1 END) as total_cancelled,
                SUM(CASE WHEN status='completed' THEN total_duration_minutes END) as total_duration,
                COUNT(CASE WHEN status='completed' THEN 1 END) as completed_count
            ")
            ->where('driver_id', $driverId)
            ->whereNull('deleted_at')
            ->first();

        $averageDuration = $summary->completed_count > 0
            ? round($summary->total_duration / $summary->completed_count)
            : 0;

        return view('user.rekap-history', compact(
            'deliveries',
            'summary',
            'averageDuration'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $routes = Route::with(['pickupLocation', 'deliveryLocations'])
                ->where('status', 'active')
                ->whereNotNull('driver_id')
                ->get();

        return view('delivery.create', compact('routes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'route_id' => 'required|exists:routes,id',
                'driver_id' => 'required|exists:users,id',
                'notes' => 'nullable|string'
            ]);

            $route = Route::with(['pickupLocation', 'deliveryLocations'])->findOrFail($data['route_id']);
            $driver = User::findOrFail($data['driver_id']);

            // Validasi Driver
            if (!$driver->isUser()) {
                flash()->error("User yang dipilih bukan driver!");
                return back()->withInput();
            }

            if (!$driver->isActive()) {
                flash()->error("Driver yang dipilih tidak aktif!");
                return back()->withInput();
            }

            // Cek driver tidak sedang memiliki delivery aktif
            if ($driver->hasActiveDelivery()) {
                flash()->error("Driver {$driver->name} sedang memiliki delivery aktif!");
                return back()->withInput();
            }

            DB::beginTransaction();

            try {
                $delivery = Delivery::create([
                    'route_id' => $route->id,
                    'driver_id' => $data['driver_id'],
                    'status' => 'pending',
                    'notes' => $data['notes'],
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'current_location_id' => $route->pickup_location_id,
                    'current_sequence' => 0
                ]);

                // Create checkpoint for pickup
                DeliveryCheckpoint::create([
                    'delivery_id' => $delivery->id,
                    'location_id' => $route->pickup_location_id,
                    'sequence' => 0,
                    'type' => 'pickup',
                    'status' => 'pending'
                ]);

                // Create checkepoint for delivery
                foreach ($route->deliveryLocations as $index => $location) {
                    DeliveryCheckpoint::create([
                        'delivery_id' => $delivery->id,
                        'location_id' => $location->id,
                        'sequence' => $index + 1,
                        'type' => 'delivery',
                        'status' => 'pending'
                    ]);
                }

                DB::commit();

                flash()->success("Delivery {$delivery->delivery_code} berhasil dibuat!");

                return redirect()->route('admin.deliveryIndex');
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            flash()->error("Gagal membuat delivery! " . $e->getMessage());

            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Delivery $delivery)
    {
        $delivery->load([
            'route.pickupLocation',
            'route.deliveryLocations',
            'driver',
            'checkpoints.location',
            'checkpoints.checkpointPhotos',
            'gpsTracking' => function ($query) {
                $query->latest('recorded_at')->limit(50);
            }
        ]);

        return view('delivery.show', compact('delivery'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function cancel(Request $request, Delivery $delivery)
    {
        try {
            $data = $request->validate([
                'cancellation_reason' => 'required|string'
            ]);

            if (!$delivery->canCancel()) {
                flash()->error("Delivery tidak dapat dibatalkan");
                return back();
            }

            $delivery->cancel($data['cancellation_reason']);
            $delivery->update(['updated_by' => Auth::id()]);

            flash()->success("Delivery {$delivery->delivery_code} berhasil dibatalkan");
            return redirect()->route('admin.deliveryIndex');
        } catch (\Exception $e) {
            flash()->error("Gagal membatalkan delivery!");
            return back();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function getCurrentLocation(Delivery $delivery)
    {
        return response()->json([
            'latitude' => $delivery->current_latitude,
            'longitude' => $delivery->current_longitude,
            'last_update' => $delivery->last_location_update?->diffForHumans(),
            'status' => $delivery->status,
            'current_checkpoint' => $delivery->getCurrentCheckpoint()?->location->name
        ]);
    }

    public function getGpsHistory(Delivery $delivery)
    {
        $history = $delivery->gpsTracking()
                ->latest('recorded_at')
                ->limit(100)
                ->get()
                ->map(function ($track) {
                    return [
                        'lat' => (float) $track->latitude,
                        'lng' => (float) $track->longitude,
                        'speed' => $track->speed,
                        'time' => $track->recorded_at->format('H:i:s')
                    ];
                });

        return response()->json($history);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Delivery $delivery)
    {
        try {
            // Boleh dihapus jika statusnya pending
            if ($delivery->status !== 'pending') {
                flash()->error("Hanya delivery dengan status pending yang dapat dihapus!");
                return back();
            }

            $deliveryCode = $delivery->delivery_code;
            $delivery->delete();

            flash()->success("Delivery {$deliveryCode} berhasil dihapus");
            return redirect()->route('admin.deliveryIndex');
        } catch (\Exception $e) {
            flash()->error("Gagal menghapus delivery!");
            return back();
        }
    }
}
