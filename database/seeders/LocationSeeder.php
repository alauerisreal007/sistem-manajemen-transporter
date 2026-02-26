<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            // Pickup location
            [
                'name' => 'Pabrik KBU',
                'type' => 'pickup',
                'address' => 'Krakal Arum, RT.5/RW.12, Bibis, Jungke, Kec. Karanganyar',
                'postal_code' => '53921',
                'latitude' => '-7.6079149702073465',
                'longitude' => '110.94580573712845',
                'city' => 'Karanganyar',
                'province' => 'Jawa Tengah',
                'status' => 'active'
            ],

            // Delivery Location
            [
                'name' => 'Outlet Semarang',
                'type' => 'delivery',
                'address' => 'Jl. Raya Sekaran, Patemon, Kec. Gn. Pati, Kota Semarang, Jawa Tengah 50228',
                'postal_code' => '53921',
                'latitude' => '-7.057153232082654',
                'longitude' => '110.39572593711961',
                'city' => 'Semarang',
                'province' => 'Jawa Tengah',
                'status' => 'active'
            ],
            [
                'name' => 'Outlet Karanganyar',
                'type' => 'delivery',
                'address' => 'Jl. Dr. Muwardi, Cangakan Timur, Cangakan, Kec. Karanganyar, Kabupaten Karanganyar, Jawa Tengah 57712',
                'postal_code' => '53921',
                'latitude' => '-7.592911227663437',
                'longitude' => '110.94191886766947',
                'city' => 'Karanganyar',
                'province' => 'Jawa Tengah',
                'status' => 'active'
            ],
            [
                'name' => 'Outlet Jogokariyan',
                'type' => 'delivery',
                'address' => 'Jl. Jend. Sudirman No.70, Bantul Wr., Bantul, Kec. Bantul, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55711',
                'postal_code' => '53921',
                'latitude' => '-7.884619822949288',
                'longitude' => '110.33141385084647',
                'city' => 'Bantul',
                'province' => 'Yogyakarta',
                'status' => 'active'
            ]
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
