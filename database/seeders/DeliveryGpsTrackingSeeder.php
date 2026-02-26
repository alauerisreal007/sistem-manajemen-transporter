<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryGpsTrackingSeeder extends Seeder
{
    public function run(): void
    {
        $deliveries = DB::table('deliveries')->get();

        foreach ($deliveries as $delivery) {
            foreach (range(1, 20) as $i) {
                DB::table('delivery_gps_trackings')->insert([
                    'delivery_id' => $delivery->id,
                    'driver_id' => $delivery->driver_id,

                    'latitude' => -6.200000 + rand(-200, 200) / 10000,
                    'longitude' => 106.816666 + rand(-200, 200) / 10000,
                    'speed' => rand(20, 60),
                    'accuracy' => rand(5, 20),
                    'heading' => rand(0, 360),
                    'battery_level' => rand(30, 100),

                    'recorded_at' => Carbon::now()->subMinutes(20 - $i),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
