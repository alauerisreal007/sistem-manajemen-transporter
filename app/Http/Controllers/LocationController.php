<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        // Logic to retrieve and return locations
        $query = Location::with(['pickupRoutes', 'deliveryRoutes']);

        // Filtering status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Searching by name or address
        if ($request->has('search') && $request->search != '') {
            $searchTerms = $request->search;
            $query->where(function ($q) use ($searchTerms) {
                $q->where('name', 'like', '%' . $searchTerms . '%')
                  ->orWhere('address', 'like', '%' . $searchTerms . '%');
            });
        }

        $location = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('location.index', compact('location'));
    }

    public function create()
    {
        return view('location.create');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:pickup,delivery',
                'address' => 'required|string|max:500',
                'postal_code' => 'required|string|max:20',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'city' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'phone' => 'nullable|string|max:20',
                'notes' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);

            $location = Location::create($data);

            $typeText = $data['type'] === 'pickup' ? 'Pickup' : 'Delivery';

            flash()->success("Lokasi {$location->name} ({$typeText}) berhasil ditambahkan!");

            return redirect()->route('admin.locationIndex');
        } catch (\Illuminate\Validation\ValidationException $e) {
            flash()->error('Data tidak valid, silahkan periksa input Anda!');

            return back()->withErrors($e->errors())->withInput();
        } catch(\Exception $e) {
            flash()->error('Gagal menambahkan lokasi, silahkan coba lagi!');

            return back()->withInput();
        }
        
    }

    public function show(Location $location)
    {
        $location->load(['pickupRoutes', 'deliveryRoutes']);
        return view('location.show', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:pickup,delivery',
                'address' => 'required|string|max:500',
                'postal_code' => 'required|string|max:20',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'city' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'status' => 'required|in:active,inactive',
            ]);

            $oldName = $location->name;
            $location->update($data);

            flash()->success("Lokasi {$oldName} berhasil diupdate!");

            return redirect()->route('admin.locationIndex');
        } catch (\Illuminate\Validation\ValidationException $e) {
            flash()->error('Data tidak valid, periksa koordinat dan data lainnya!');

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            flash()->error('Gagal mengupdate lokasi!');

            return back()->withInput();
        }
        
    }

    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $oldName = $location->name;

        $pickupCount = $location->pickupRoutes()->count();
        $deliveryCount = $location->deliveryRoutes()->count();

        if ($pickupCount > 0 || $deliveryCount > 0) {
            $totalCount = $pickupCount + $deliveryCount;
            flash()->warning("Tidak dapat menghapus lokasi {$oldName} karena masih digunakan di {$totalCount} rute lainnya!");

            return back();
        }

        $location->delete();

        flash()->success("Lokasi berhasil dihapus!, 📍 '{$oldName}' telah dihapus dari sistem");

        return redirect()->route('admin.locationIndex');
        
    }
    public function getActiveLocations()
    {
        $location = Location::active()->get();
        return response()->json($location);
    }
}
