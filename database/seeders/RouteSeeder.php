<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Route;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pickupKBU = Location::where('name', 'Pabrik KBU')->first();
        $outletSemarang = Location::where('name', 'Outlet Semarang')->first();
        $outletKaranganyar = Location::where('name', 'Outlet Karanganyar')->first();
        $outletJogokariyan = Location::where('name', 'Outlet Bantul')->first();

        $admin = User::where('role', 'admin')->first();

        $routes = [
            [
                'route_name' => 'Karanganyar - Semarang',
                'pickup_location_id' => $pickupKBU->id,
                'delivery_location_id' => $outletSemarang->id,
                'distance_km' => 110.00,
                'estimated_time' => 173,
                'status' => 'active',
                'created_by' => $admin->id
            ],
            [
                'route_name' => 'Karanganyar - Karanganyar',
                'pickup_location_id' => $pickupKBU->id,
                'delivery_location_id' => $outletKaranganyar->id,
                'distance_km' => 2.8,
                'estimated_time' => 7,
                'status' => 'active',
                'created_by' => $admin->id
            ],
            [
                'route_name' => 'Karanganyar - Bantul',
                'pickup_location_id' => $pickupKBU->id,
                'delivery_location_id' => $outletJogokariyan->id,
                'distance_km' => 90.1,
                'estimated_time' => 143,
                'status' => 'active',
                'created_by' => $admin->id
            ]
        ];

        foreach ($routes as $route) {
            Route::create($route);
        }
    }
}
