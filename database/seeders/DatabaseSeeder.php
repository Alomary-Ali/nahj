<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Core Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $taRole = Role::firstOrCreate(['name' => 'ta']);

        // 2. Create Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@nahj.com'],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($adminRole);

        // 3. Create Demo Instructor
        $instructor = User::firstOrCreate(
            ['email' => 'instructor@nahj.com'],
            [
                'name' => 'Dr. Andrew Ng (Demo)',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $instructor->assignRole($instructorRole);

        // 4. Create Demo Student
        $student = User::firstOrCreate(
            ['email' => 'student@nahj.com'],
            [
                'name' => 'Student Learner',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $student->assignRole($studentRole);

        // 5. Seed Curriculum Data
        $this->call(CurriculumSeeder::class);
    }
}
