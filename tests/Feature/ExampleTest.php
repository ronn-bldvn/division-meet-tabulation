<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_single_elimination_winners_advance_by_match_number(): void
    {
        $sport = Sport::create(['name' => 'Basketball', 'slug' => 'basketball']);
        $game = Game::create([
            'sport_id' => $sport->id,
            'name' => 'Open Division',
            'bracket_type' => 'single_elimination',
        ]);
        $facilitator = User::create([
            'name' => 'Facilitator',
            'email' => 'facilitator@example.com',
            'password' => 'password',
            'role' => 'facilitator',
            'sport_id' => $sport->id,
        ]);
        $teams = collect(['Alpha', 'Bravo', 'Charlie', 'Delta'])->map(fn ($name, $index) => Team::create([
            'name' => $name,
            'code' => 'hsteam'.($index + 1),
        ]));
        $firstMatch = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'elimination',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[1]->id,
        ]);
        $secondMatch = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'elimination',
            'match_number' => 2,
            'team1_id' => $teams[2]->id,
            'team2_id' => $teams[3]->id,
        ]);

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $firstMatch]), [
            'winner_id' => $teams[0]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $secondMatch]), [
            'winner_id' => $teams[3]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('game_matches', [
            'game_id' => $game->id,
            'round' => 'semifinal',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[3]->id,
            'status' => 'scheduled',
        ]);

        $semifinal = GameMatch::where('game_id', $game->id)
            ->where('round', 'semifinal')
            ->where('match_number', 1)
            ->firstOrFail();

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $semifinal]), [
            'winner_id' => $teams[3]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('game_matches', [
            'game_id' => $game->id,
            'round' => 'final',
            'match_number' => 1,
            'team1_id' => $teams[3]->id,
            'status' => 'scheduled',
        ]);

    }

    public function test_round_robin_results_do_not_advance_winners(): void
    {
        $sport = Sport::create(['name' => 'Volleyball', 'slug' => 'volleyball']);
        $game = Game::create([
            'sport_id' => $sport->id,
            'name' => 'Open Division',
            'bracket_type' => 'round_robin',
        ]);
        $facilitator = User::create([
            'name' => 'Facilitator',
            'email' => 'round-robin@example.com',
            'password' => 'password',
            'role' => 'facilitator',
            'sport_id' => $sport->id,
        ]);
        $teams = collect(['Alpha', 'Bravo'])->map(fn ($name, $index) => Team::create([
            'name' => $name,
            'code' => 'hsteam'.($index + 1),
        ]));
        $match = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'elimination',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[1]->id,
        ]);

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $match]), [
            'winner_id' => $teams[0]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseCount('game_matches', 1);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'gold', 'team_id' => $teams[0]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'silver', 'team_id' => $teams[1]->id]);
    }
}
