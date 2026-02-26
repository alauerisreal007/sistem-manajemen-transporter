<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $checkpoints = DB::table('delivery_checkpoints')->get();

        foreach ($checkpoints as $checkpoint) {
            DB::table('delivery_photos')->insert([
                'delivery_id' => $checkpoint->delivery_id,
                'checkpoint_id' => $checkpoint->id,

                'photo_path' => 'uploads/delivery/sample_' . rand(1, 5) . '.jpg',
                'photo_type' => $checkpoint->type === 'pickup' ? 'pickup' : 'delivery',

                'latitude' => -6.2 + rand(-100, 100) / 10000,
                'longitude' => 106.8 + rand(-100, 100) / 10000,

                'captured_at' => Carbon::now()->subMinutes(rand(5, 30)),
                'caption' => 'Foto bukti pengiriman',

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
