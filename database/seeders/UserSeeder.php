<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'dimas',
            'fullname' => 'Dimas Habibi H', // Optional, based on SQL dump context or just generic
            'email' => 'dimsshidayat28@gmail.com', // Optional, based on SQL dump context
            'password' => Hash::make('dimas123'),
            'status' => 'admin',
        ]);
    }
}
