<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportsMeetEventsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_meet_events_idempotently(): void
    {
        $seeder = app(DatabaseSeeder::class);
        $seeder->run();
        $seeder->run();

        $this->assertDatabaseCount('sports', 16);
        $this->assertDatabaseCount('games', 190);
        $this->assertDatabaseCount('users', 17);
        $this->assertDatabaseCount('teams', 8);

        $athletics = Sport::where('name', 'Athletics')->firstOrFail();
        $this->assertDatabaseHas('games', [
            'sport_id' => $athletics->id,
            'name' => '4 x 400 relay',
            'category' => 'Girls',
            'level' => 'elementary',
            'bracket_type' => 'none',
        ]);

        $badminton = Sport::where('name', 'Badminton')->firstOrFail();
        $this->assertSame(12, Game::where('sport_id', $badminton->id)->count());

        $futsal = Sport::where('name', 'Futsal')->firstOrFail();
        $this->assertDatabaseHas('games', [
            'sport_id' => $futsal->id,
            'name' => 'Futsal',
            'category' => 'Girls',
            'level' => 'high_school',
        ]);

        $billiards = Sport::where('name', 'Billiards')->firstOrFail();
        $this->assertDatabaseHas('games', [
            'sport_id' => $billiards->id,
            'name' => 'Billiards',
            'category' => 'Boys',
            'level' => 'high_school',
        ]);
    }
}
