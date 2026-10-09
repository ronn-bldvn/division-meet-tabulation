<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomaticMedalAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_elimination_awards_medals_from_final_and_third_place_winners(): void
    {
        [$game, $facilitator, $teams] = $this->makeGame('single_elimination');
        GameMatch::create([
            'game_id' => $game->id,
            'round' => 'semifinal',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[1]->id,
            'winner_id' => $teams[0]->id,
            'status' => 'completed',
        ]);
        GameMatch::create([
            'game_id' => $game->id,
            'round' => 'semifinal',
            'match_number' => 2,
            'team1_id' => $teams[2]->id,
            'team2_id' => $teams[3]->id,
            'winner_id' => $teams[2]->id,
            'status' => 'completed',
        ]);
        $final = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'final',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[2]->id,
        ]);
        $thirdPlace = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'third_place',
            'match_number' => 1,
            'team1_id' => $teams[1]->id,
            'team2_id' => $teams[3]->id,
        ]);

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $final]), [
            'winner_id' => $teams[2]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $thirdPlace]), [
            'winner_id' => $teams[3]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'gold', 'team_id' => $teams[2]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'silver', 'team_id' => $teams[0]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'bronze', 'team_id' => $teams[3]->id]);
    }

    public function test_round_robin_awards_medals_using_head_to_head_to_break_a_wins_tie(): void
    {
        [$game, $facilitator, $teams] = $this->makeGame('round_robin');

        $results = [
            [$teams[0], $teams[1], $teams[0]],
            [$teams[0], $teams[1], $teams[0]],
            [$teams[0], $teams[1], $teams[1]],
            [$teams[0], $teams[2], $teams[0]],
            [$teams[1], $teams[2], $teams[1]],
            [$teams[1], $teams[3], $teams[1]],
            [$teams[2], $teams[3], $teams[2]],
        ];

        foreach ($results as $index => [$team1, $team2, $winner]) {
            GameMatch::create([
                'game_id' => $game->id,
                'round' => 'round_robin',
                'match_number' => $index + 1,
                'team1_id' => $team1->id,
                'team2_id' => $team2->id,
                'winner_id' => $winner->id,
                'status' => 'completed',
            ]);
        }

        $lastMatch = $game->matches()->latest('id')->firstOrFail();
        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $lastMatch]), [
            'winner_id' => $lastMatch->winner_id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'gold', 'team_id' => $teams[0]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'silver', 'team_id' => $teams[1]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'bronze', 'team_id' => $teams[2]->id]);
    }

    public function test_double_elimination_routes_winners_and_losers_and_awards_reset_final_medals(): void
    {
        [$game, $facilitator, $teams] = $this->makeGame('double_elimination');

        $winnersRound = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'winners_round_1',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[1]->id,
        ]);
        $secondWinnersRound = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'winners_round_1',
            'match_number' => 2,
            'team1_id' => $teams[2]->id,
            'team2_id' => $teams[3]->id,
            'winner_id' => $teams[3]->id,
            'status' => 'completed',
        ]);
        $winnersFinal = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'winners_round_2',
            'match_number' => 1,
            'team2_id' => $teams[3]->id,
        ]);
        $losersRound = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'losers_round_1',
            'match_number' => 1,
            'team2_id' => $teams[2]->id,
        ]);
        $losersFinal = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'losers_final',
            'match_number' => 1,
        ]);
        $grandFinal = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'grand_final',
            'match_number' => 1,
        ]);
        $winnersFinal->update([
            'winner_next_match_id' => $grandFinal->id,
            'winner_next_slot' => 'team1',
        ]);
        $this->actingAs($facilitator)->get(route('facilitator.matches.index', $game))
            ->assertOk()
            ->assertSee('Loser advances to');

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $winnersRound]), [
            'round' => 'winners_round_1',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[1]->id,
            'winner_id' => $teams[0]->id,
            'status' => 'completed',
            'winner_next_match_id' => $winnersFinal->id,
            'winner_next_slot' => 'team1',
            'loser_next_match_id' => $losersRound->id,
            'loser_next_slot' => 'team1',
        ])->assertRedirect();

        $this->assertDatabaseHas('game_matches', [
            'id' => $winnersFinal->id,
            'team1_id' => $teams[0]->id,
        ]);
        $this->assertDatabaseHas('game_matches', [
            'id' => $losersRound->id,
            'team1_id' => $teams[1]->id,
        ]);

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $losersRound]), [
            'winner_id' => $teams[2]->id,
            'status' => 'completed',
            'winner_next_match_id' => $losersFinal->id,
            'winner_next_slot' => 'team1',
        ])->assertRedirect();
        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $winnersFinal]), [
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[3]->id,
            'winner_id' => $teams[0]->id,
            'status' => 'completed',
            'winner_next_match_id' => $grandFinal->id,
            'winner_next_slot' => 'team1',
            'loser_next_match_id' => $losersFinal->id,
            'loser_next_slot' => 'team2',
        ])->assertRedirect();
        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $losersFinal]), [
            'winner_id' => $teams[2]->id,
            'status' => 'completed',
            'winner_next_match_id' => $grandFinal->id,
            'winner_next_slot' => 'team2',
        ])->assertRedirect();
        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $grandFinal]), [
            'winner_id' => $teams[2]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $reset = $game->matches()->where('round', 'grand_final_reset')->firstOrFail();
        $this->assertSame($teams[0]->id, $reset->team1_id);
        $this->assertSame($teams[2]->id, $reset->team2_id);
        $this->assertSame('scheduled', $reset->status);

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $reset]), [
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[2]->id,
            'winner_id' => $teams[0]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'gold', 'team_id' => $teams[0]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'silver', 'team_id' => $teams[2]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'bronze', 'team_id' => $teams[3]->id]);
    }

    public function test_unbracketed_medals_only_allow_teams_for_the_event_level(): void
    {
        [$game, $facilitator, $highSchoolTeams] = $this->makeGame('none');
        $elementaryTeam = Team::create(['name' => 'Elementary 1', 'code' => 'elemteam1']);
        $highSchoolTeam = $highSchoolTeams[0];
        $highSchoolTeam->update(['name' => 'High School 1']);

        $this->actingAs($facilitator)->get(route('facilitator.medals.index', $game))
            ->assertOk()
            ->assertSee('Save All Medals')
            ->assertDontSee('Save Gold')
            ->assertDontSee('Save Silver')
            ->assertDontSee('Save Bronze');

        $this->actingAs($facilitator)->post(route('facilitator.medals.store', $game), [
            'medals' => [
                'gold' => ['team_id' => $elementaryTeam->id],
                'silver' => ['team_id' => $highSchoolTeams[1]->id],
                'bronze' => ['team_id' => $highSchoolTeams[2]->id],
            ],
        ])->assertSessionHasErrors('medals');

        $this->actingAs($facilitator)->post(route('facilitator.medals.store', $game), [
            'medals' => [
                'gold' => ['team_id' => $highSchoolTeam->id],
                'silver' => ['team_id' => $highSchoolTeams[1]->id],
                'bronze' => ['team_id' => $highSchoolTeams[2]->id],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('status', 'All medals saved.');

        $this->assertDatabaseHas('medals', [
            'game_id' => $game->id,
            'type' => 'gold',
            'team_id' => $highSchoolTeam->id,
        ]);
        $this->assertDatabaseHas('medals', [
            'game_id' => $game->id,
            'type' => 'silver',
            'team_id' => $highSchoolTeams[1]->id,
        ]);
        $this->assertDatabaseHas('medals', [
            'game_id' => $game->id,
            'type' => 'bronze',
            'team_id' => $highSchoolTeams[2]->id,
        ]);
        $this->get('/?level=high_school')->assertOk()->assertSee('High School 1')->assertDontSee('Elementary 1');
    }

    public function test_single_elimination_medals_can_be_manually_selected_and_protected_from_match_updates(): void
    {
        [$game, $facilitator, $teams] = $this->makeGame('single_elimination');
        $final = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'final',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[1]->id,
        ]);

        $this->actingAs($facilitator)->get(route('facilitator.medals.index', $game))
            ->assertOk()
            ->assertSee('Save All Medals')
            ->assertSee('manually select the podium');

        $this->actingAs($facilitator)->post(route('facilitator.medals.store', $game), [
            'medals' => [
                'gold' => ['team_id' => $teams[2]->id],
                'silver' => ['team_id' => $teams[0]->id],
                'bronze' => ['team_id' => $teams[3]->id],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $final]), [
            'winner_id' => $teams[1]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('games', ['id' => $game->id, 'manual_medals' => true]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'gold', 'team_id' => $teams[2]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'silver', 'team_id' => $teams[0]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'bronze', 'team_id' => $teams[3]->id]);
    }

    public function test_double_elimination_medals_can_be_manually_selected(): void
    {
        [$game, $facilitator, $teams] = $this->makeGame('double_elimination');
        $final = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'grand_final',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[1]->id,
        ]);

        $this->actingAs($facilitator)->get(route('facilitator.medals.index', $game))
            ->assertOk()
            ->assertSee('Save All Medals');

        $this->actingAs($facilitator)->post(route('facilitator.medals.store', $game), [
            'medals' => [
                'gold' => ['team_id' => $teams[1]->id],
                'silver' => ['team_id' => $teams[2]->id],
                'bronze' => ['team_id' => $teams[0]->id],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('games', ['id' => $game->id, 'manual_medals' => true]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'gold', 'team_id' => $teams[1]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'silver', 'team_id' => $teams[2]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'bronze', 'team_id' => $teams[0]->id]);

        $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $final]), [
            'winner_id' => $teams[0]->id,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'gold', 'team_id' => $teams[1]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'silver', 'team_id' => $teams[2]->id]);
        $this->assertDatabaseHas('medals', ['game_id' => $game->id, 'type' => 'bronze', 'team_id' => $teams[0]->id]);

        $this->actingAs($facilitator)->post(route('facilitator.medals.automatic', $game))
            ->assertRedirect()
            ->assertSessionHas('status', 'Automatic medal assignment will resume when the next match result is saved.');

        $this->assertDatabaseHas('games', ['id' => $game->id, 'manual_medals' => false]);
    }

    public function test_facilitator_can_edit_bracket_format_before_matches_are_added(): void
    {
        [$game, $facilitator] = $this->makeGame('none');

        $this->actingAs($facilitator)->put(route('facilitator.bracket.update', $game), [
            'bracket_type' => 'double_elimination',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('games', ['id' => $game->id, 'bracket_type' => 'double_elimination']);
    }

    public function test_round_robin_does_not_assign_medals_when_head_to_head_is_tied(): void
    {
        [$game, $facilitator, $teams] = $this->makeGame('round_robin');
        GameMatch::create([
            'game_id' => $game->id,
            'round' => 'round_robin',
            'match_number' => 1,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[1]->id,
            'winner_id' => $teams[0]->id,
            'status' => 'completed',
        ]);
        $second = GameMatch::create([
            'game_id' => $game->id,
            'round' => 'round_robin',
            'match_number' => 2,
            'team1_id' => $teams[0]->id,
            'team2_id' => $teams[1]->id,
            'winner_id' => $teams[1]->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($facilitator)->put(route('facilitator.matches.update', [$game, $second]), [
            'winner_id' => $teams[1]->id,
            'status' => 'completed',
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Matches are complete, but head-to-head results do not resolve a tie. Medals for tied places remain unassigned.');
        $this->assertDatabaseCount('medals', 0);
    }

    private function makeGame(string $bracketType): array
    {
        $sport = Sport::create(['name' => 'Test Sport', 'slug' => 'test-sport']);
        $game = Game::create([
            'sport_id' => $sport->id,
            'name' => 'Test Event',
            'level' => 'high_school',
            'bracket_type' => $bracketType,
        ]);
        $facilitator = User::create([
            'name' => 'Facilitator',
            'email' => $bracketType.'@example.com',
            'password' => 'password',
            'role' => 'facilitator',
            'sport_id' => $sport->id,
        ]);
        $teams = collect(['Alpha', 'Bravo', 'Charlie', 'Delta'])->map(fn ($name, $index) => Team::create([
            'name' => $name,
            'code' => 'hsteam'.($index + 1),
        ]));

        return [$game, $facilitator, $teams];
    }
}
