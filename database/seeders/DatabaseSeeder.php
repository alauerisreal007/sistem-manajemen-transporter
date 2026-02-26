<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminAccountSeeder::class,
            LocationSeeder::class,
            // RouteSeeder::class,
            // DeliverySeeder::class,
            // DeliveryCheckpointSeeder::class,
            // DeliveryGpsTrackingSeeder::class,
            // DeliveryPhotoSeeder::class
        ]);
    }
}
