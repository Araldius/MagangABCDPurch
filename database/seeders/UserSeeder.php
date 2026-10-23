<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan tabel users (optional tapi bagus untuk fresh test)
        try { \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF'); }
        catch (\Exception $e) { \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0'); }
        \Illuminate\Support\Facades\DB::table('users')->truncate();
        try { \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON'); }
        catch (\Exception $e) { \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1'); }

        \App\Models\User::create([
            'name' => 'Admin Purchasing', 'email' => 'admin@company.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'), 'role' => 'purchasing', 'department' => 'Purchasing',
        ]);

        \App\Models\User::create(['name' => 'John Requester', 'email' => 'user@company.com', 'password' => \Illuminate\Support\Facades\Hash::make('password'), 'role' => 'user', 'department' => 'Operations']);
        \App\Models\User::create(['name' => 'Budi Santoso', 'email' => 'budi@company.com', 'password' => \Illuminate\Support\Facades\Hash::make('password'), 'role' => 'user', 'department' => 'Maintenance']);
        \App\Models\User::create(['name' => 'Siti Aminah', 'email' => 'siti@company.com', 'password' => \Illuminate\Support\Facades\Hash::make('password'), 'role' => 'user', 'department' => 'Quality Control']);
    }
}
