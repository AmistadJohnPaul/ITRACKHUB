<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;


class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@itrackhub.test'],
            [
                'name' => 'ITrackHub Admin',
                'password' => ('password123'),
                'role' => 'admin',
            ]
        );
    }
}