<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryCheckpointSeeder extends Seeder
{
    public function run(): void
    {
        $deliveries = DB::table('deliveries')->get();
        $locations = DB::table('locations')->pluck('id');

        foreach ($deliveries as $delivery) {
            foreach (range(0, 2) as $seq) {
                DB::table('delivery_checkpoints')->insert([
                    'delivery_id' => $delivery->id,
                    'location_id' => $locations->random(),
                    'sequence' => $seq,
                    'type' => $seq === 0 ? 'pickup' : 'delivery',
                    'status' => 'pending',

                    // 'arrived_at' => Carbon::now()->subMinutes(60),
                    // 'load_start_at' => Carbon::now()->subMinutes(55),
                    // 'load_end_at' => Carbon::now()->subMinutes(45),
                    // 'departed_at' => Carbon::now()->subMinutes(40),
                    // 'load_duration_minutes' => 10,

                    // 'arrival_latitude' => -6.2 + rand(-100, 100) / 10000,
                    // 'arrival_longitude' => 106.8 + rand(-100, 100) / 10000,

                    'recipient_name' => 'Penerima ' . $seq,
                    'notes' => 'Checkpoint normal',

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
