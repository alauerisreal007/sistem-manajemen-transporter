<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        $routes = DB::table('routes')->pluck('id');
        $drivers = DB::table('users')->where('role', 'user')->pluck('id');
        $locations = DB::table('locations')->pluck('id');

        foreach (range(1, 10) as $i) {
            DB::table('deliveries')->insert([
                'delivery_code' => 'DLV-' . strtoupper(Str::random(8)),
                'route_id' => $routes->random(),
                'driver_id' => $drivers->random(),
                'status' => 'pending',

                'started_at' => Carbon::now()->subMinutes(rand(30, 120)),
                // 'current_location_id' => $locations->random(),
                'current_sequence' => rand(1, 3),

                'current_latitude' => -6.200000 + rand(-100, 100) / 10000,
                'current_longitude' => 106.816666 + rand(-100, 100) / 10000,
                'last_location_update' => now(),

                'notes' => 'Pengiriman berjalan normal',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
