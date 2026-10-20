<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;


class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'staff@itrackhub.test'],
            [
                'name' => 'ITrackHub Staff',
                'password' => ('password123'),
                'role' => 'staff',
            ]
        );
    }
}