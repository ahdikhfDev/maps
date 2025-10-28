<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Beberapa kategori umum di Bogor
        $categories = [
            'Restoran', 'Kafe', 'Taman', 'Hotel', 'Museum', 
            'Toko', 'Kantor', 'Gym', 'Rumah Sakit', 'Sekolah', 'mal', 'wisata'
        ];

        // Gambar tetap dari URL kamu

        for ($i = 0; $i < 10000; $i++) {
            DB::table('locations')->insert([
                'name' => 'Lokasi ' . $faker->company,
                'address' => $faker->streetAddress . ', Bogor, Jawa Barat',
                'category' => $faker->randomElement($categories),
                'latitude' => $faker->latitude(-6.7, -6.5),  // sekitar Bogor
                'longitude' => $faker->longitude(106.7, 107.0),
                'description' => $faker->sentence(12),
                'image' => null,
                'is_active' => $faker->boolean(90),
                'created_at' => now(),
                'updated_at' => now(),  
            ]);
        }
    }
}
