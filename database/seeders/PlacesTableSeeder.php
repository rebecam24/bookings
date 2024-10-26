<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlacesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('places')->insert([
            [
                'name' => 'Main Auditorium',
                'description' => 'A large auditorium suitable for conferences and big events.',
                'images' => json_encode(['auditorium1.jpg', 'auditorium2.jpg']),
                'capacity' => 300,
                'available_from' => Carbon::now()->toDateString(),
                'available_to' => Carbon::now()->addMonths(6)->toDateString(),
                'type' => 'auditorio',
                'active' => true,
                'default_hours' => '08:00-18:00',
                'default_days' => json_encode(['Lun', 'Mar', 'Mie', 'Jue', 'Vie']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Conference Room A',
                'description' => 'A medium-sized conference room for meetings and workshops.',
                'images' => json_encode(['conference1.jpg', 'conference2.jpg']),
                'capacity' => 50,
                'available_from' => Carbon::now()->toDateString(),
                'available_to' => Carbon::now()->addMonths(3)->toDateString(),
                'type' => 'sala de conferencia',
                'active' => true,
                'default_hours' => '09:00-17:00',
                'default_days' => json_encode(['Lun', 'Mie', 'Vie']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Meeting Room B',
                'description' => 'A small meeting room for private discussions and small groups.',
                'images' => json_encode(['meeting1.jpg']),
                'capacity' => 10,
                'available_from' => Carbon::now()->toDateString(),
                'available_to' => Carbon::now()->addMonths(2)->toDateString(),
                'type' => 'sala de reunion',
                'active' => true,
                'default_hours' => '10:00-16:00',
                'default_days' => json_encode(['Mar', 'Jue']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Meeting Room C',
                'description' => 'A small meeting room for private discussions and small groups.',
                'images' => json_encode(['meeting2.jpg']),
                'capacity' => 40,
                'available_from' => Carbon::now()->toDateString(),
                'available_to' => Carbon::now()->addMonths(2)->toDateString(),
                'type' => 'sala de reunion',
                'active' => true,
                'default_hours' => '10:00-16:00',
                'default_days' => json_encode(['Mar', 'Jue']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}