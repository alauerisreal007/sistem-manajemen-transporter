<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Route;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RouteController extends Controller
{
    //

    public function index(Request $request)
    {
        $query = Route::with(['pickupLocation', 'deliveryLocation', 'deliveryLocations', 'creator', 'updater']);

        // filtering
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // searching
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('route_name', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('pickupLocation', function ($loc) use ($searchTerm) {
                        $loc->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('deliveryLocations', function ($loc) use ($searchTerm) {
                        $loc->where('name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        $route = $query->orderBy('created_at', 'desc')->paginate(10);

        $locations = Location::active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('route.index', compact('route', 'locations'));
    }

    public function create()
    {
        $locations = Location::active()->get();
        return view('route.create', compact('locations'));
    }
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'route_name' => 'required|string|max:255',
            'pickup_location_id' => 'required|exists:locations,id',
            'delivery_location_ids' => 'required|array|min:1',
            'delivery_location_ids.*' => 'required|exists:locations,id|different:pickup_location_id',
            'distance_km' => 'required|numeric|min:0',
            'estimated_time' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive,completed',
        ], [
            'pickup_location_id.exists' => 'The selected pickup location is invalid.',
            'delivery_location_ids.required' => 'Minimal 1 lokasi delivery harus dipilih',
            'delivery_location_ids.*.exists' => 'The selected delivery location is invalid.',
            'delivery_location_ids.*.different' => 'The delivery location must be different from the pickup location.'
        ]);

        // Validasi tidak ada duplikat di lokasi delivery
        $deliveryIds = $data['delivery_location_ids'];
        if (count($deliveryIds) !== count(array_unique($deliveryIds))) {
            flash()->error('Tidak boleh memilih lokasi delivery yang sama lebih dari sekali!');

            return back()->withInput();
        }

        if (in_array($data['pickup_location_id'], $deliveryIds)) {
            flash()->warning('Lokasi delivery tidak boleh sama dengan lokasi pickup!');

            return back()->withInput();
        }

        $route = Route::create([
            'route_name' => $data['route_name'],
            'pickup_location_id' => $data['pickup_location_id'],
            'delivery_location_id' => $deliveryIds[0],
            'distance_km' => $data['distance_km'],
            'estimated_time' => $data['estimated_time'],
            'status' => $data['status'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        foreach ($deliveryIds as $sequence => $locationId) {
            $route->deliveryLocations()->attach($locationId, [
                'sequence' => $sequence + 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        flash()->success("Rute berhasil dibuat! '{$route->route_name}' dengan " . count($deliveryIds) . " titik delivery");

        return redirect()->route('admin.routeIndex');
    }


    public function show(Route $route)
    {
        if (!$route->relationLoaded('pickupLocation')) {
            $route->load(['pickupLocation', 'deliveryLocation', 'deliveryLocations', 'creator', 'updater']);
        }

        return view('route.show', compact('route'));
    }


    public function update(Request $request, Route $route)
    {
        $data = $request->validate([
            'route_name' => 'required|string|max:255',
            'pickup_location_id' => 'required|exists:locations,id',
            'delivery_location_ids' => 'required|array|min:1',
            'delivery_location_ids.*' => 'required|exists:locations,id|different:pickup_location_id',
            'distance_km' => 'required|numeric|min:0',
            'estimated_time' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive,completed',
        ], [
            'pickup_location_id.exists' => 'The selected pickup location is invalid.',
            'delivery_location_ids.required' => 'Minimal 1 lokasi delivery harus dipilih',
            'delivery_location_ids.*.exists' => 'The selected delivery location is invalid.',
            'delivery_location_ids.*.different' => 'The delivery location must be different from the pickup location.'
        ]);

        // Validasi tidak ada duplikat di lokasi delivery
        $deliveryIds = $data['delivery_location_ids'];
        if (count($deliveryIds) !== count(array_unique($deliveryIds))) {
            flash()->error('Tidak boleh memilih lokasi delivery yang sama lebih dari sekali!');

            return back()->withInput();
        }

        if (in_array($data['pickup_location_id'], $deliveryIds)) {
            flash()->warning('Lokasi delivery tidak boleh sama dengan lokasi pickup!');

            return back()->withInput();
        }

        $route->update([
            'route_name' => $data['route_name'],
            'pickup_location_id' => $data['pickup_location_id'],
            'delivery_location_id' => $deliveryIds[0],
            'distance_km' => $data['distance_km'],
            'estimated_time' => $data['estimated_time'],
            'status' => $data['status'],
            'updated_by' => Auth::id()
        ]);

        $syncData = [];
        foreach ($deliveryIds as $sequence => $locationId) {
            $syncData[$locationId] = [
                'sequence' => $sequence + 1,
                'updated_at' => now()
            ];
        }

        $route->deliveryLocations()->sync($syncData);

        flash()->success("Rute {$route->route_name} berhasil diupdate dengan " . count($deliveryIds) . " titik delivery !");

        return redirect()->route('admin.routeIndex');
    }

    public function destroy(Route $route)
    {
        if ($route->hasActiveDelivery()) {
            flash()->error("Tidak dapat menghapus rute yang sedang digunakan delivery aktif!");
            return back();
        }

        $route->delete();

        flash()->success("Route deleted successfully.");
        
        return redirect()->route('admin.routeIndex');
    }

    public function getAvailableDrivers()
    {
        $drivers = User::availableDrivers()
                    ->select('id', 'name', 'email')
                    ->orderBy('name')
                    ->get()
                    ->map(function($driver) {
                        return [
                            'id' => $driver->id,
                            'name' => $driver->name,
                            'email' => $driver->email
                        ];
                    });

        return response()->json($drivers);
    }

    public function complete(Route $route)
    {
        if ($route->status === 'completed') {
            return back()->with('error', 'This route already completed');
        }

        $route->update([
            'status' => 'completed',
            'updated_by' => Auth::id()
        ]);

        return back()->with('success', 'This route has been successfully completed');
    }

    public function reactivate(Route $route)
    {
        if ($route->status === 'active') {
            return back()->with('error', 'This route already active.');
        }

        $route->update([
            'status' => 'active',
            'updated_by' => Auth::id()
        ]);

        return back()->with('success', 'This route has been successfully reactivated.');
    }
}