<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Super admin account
        User::firstOrCreate(['email' => 'admin@meet.local'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'), // change immediately after first login
            'role' => 'super_admin',
        ]);

        $this->call(SportsMeetEventsSeeder::class);

        foreach (Sport::all() as $sport) {
            User::firstOrCreate(['email' => Str::slug($sport->name).'@meet.local'], [
                'name' => $sport->name.' Facilitator',
                'password' => Hash::make('password'),
                'role' => 'facilitator',
                'sport_id' => $sport->id,
            ]);
        }

        $teams = [
            ['name' => 'Elementary Division 1', 'code' => 'elemteam1', 'color' => '#dc2626'],
            ['name' => 'Elementary Division 2', 'code' => 'elemteam2', 'color' => '#2563eb'],
            ['name' => 'Elementary Division 3', 'code' => 'elemteam3', 'color' => '#16a34a'],
            ['name' => 'Elementary Division 4', 'code' => 'elemteam4', 'color' => '#ca8a04'],
            ['name' => 'High School Division 1', 'code' => 'hsteam1', 'color' => '#dc2626'],
            ['name' => 'High School Division 2', 'code' => 'hsteam2', 'color' => '#2563eb'],
            ['name' => 'High School Division 3', 'code' => 'hsteam3', 'color' => '#16a34a'],
            ['name' => 'High School Division 4', 'code' => 'hsteam4', 'color' => '#ca8a04'],
        ];
        foreach ($teams as $team) {
            Team::updateOrCreate(['code' => $team['code']], $team);
        }
    }
}
