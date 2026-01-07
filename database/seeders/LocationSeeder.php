<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['location_name' => 'Casting', 'description' => 'Casting Area'],
            ['location_name' => 'Finishing', 'description' => 'Finishing Area'],
            ['location_name' => 'Polymodel', 'description' => 'Polymodel Area'],
            ['location_name' => 'Tryout', 'description' => 'Tryout Area'],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
