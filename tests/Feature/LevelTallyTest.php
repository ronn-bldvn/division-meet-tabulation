<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Medal;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Support\Level;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LevelTallyTest extends TestCase
{
    use RefreshDatabase;

    private function makeGame(Sport $sport, string $level, string $name): Game
    {
        return Game::create([
            'sport_id' => $sport->id,
            'name' => $name,
            'level' => $level,
            'bracket_type' => 'none',
        ]);
    }

    public function test_tally_can_be_filtered_by_level(): void
    {
        $sport = Sport::create(['name' => 'Basketball', 'slug' => 'basketball']);
        $teamA = Team::create(['name' => 'Elementary Division A', 'code' => 'elemteam1', 'color' => '#000000']);
        $teamB = Team::create(['name' => 'High School Division B', 'code' => 'hsteam1', 'color' => '#111111']);

        $elementary = $this->makeGame($sport, Level::ELEMENTARY, 'Elem Game');
        $highSchool = $this->makeGame($sport, Level::HIGH_SCHOOL, 'HS Game');

        Medal::create(['game_id' => $elementary->id, 'team_id' => $teamA->id, 'type' => 'gold']);
        Medal::create(['game_id' => $highSchool->id, 'team_id' => $teamB->id, 'type' => 'gold']);

        // Unfiltered: both medals count.
        $all = Team::tally();
        $this->assertSame(1, $all->firstWhere('id', $teamA->id)->gold_count);
        $this->assertSame(1, $all->firstWhere('id', $teamB->id)->gold_count);

        // Elementary only: only team A's elementary medal counts.
        $elementaryTally = Team::tally(Level::ELEMENTARY);
        $this->assertSame(1, $elementaryTally->firstWhere('id', $teamA->id)->gold_count);
        $this->assertNull($elementaryTally->firstWhere('id', $teamB->id));

        // High school only: only team B's high-school medal counts.
        $highTally = Team::tally(Level::HIGH_SCHOOL);
        $this->assertNull($highTally->firstWhere('id', $teamA->id));
        $this->assertSame(1, $highTally->firstWhere('id', $teamB->id)->gold_count);
    }

    public function test_unknown_level_is_ignored(): void
    {
        $sport = Sport::create(['name' => 'Basketball', 'slug' => 'basketball']);
        $team = Team::create(['name' => 'Elementary Division A', 'code' => 'elemteam1', 'color' => '#000000']);
        $game = $this->makeGame($sport, Level::ELEMENTARY, 'Elem Game');
        Medal::create(['game_id' => $game->id, 'team_id' => $team->id, 'type' => 'silver']);

        // A bogus level must not hide the medal — it falls back to the full tally.
        $tally = Team::tally('college');
        $this->assertSame(1, $tally->firstWhere('id', $team->id)->silver_count);
    }

    public function test_public_tally_page_renders_level_filter(): void
    {
        $this->withoutVite();

        Sport::create(['name' => 'Basketball', 'slug' => 'basketball']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Elementary')
            ->assertSee('High School');

        // Filtering by a level still renders, and labels the active level.
        $this->get('/?level='.Level::ELEMENTARY)
            ->assertOk()
            ->assertSee('Elementary Level');

        // A bogus level is ignored rather than erroring.
        $this->get('/?level=college')->assertOk();
    }

    public function test_sport_page_lists_games_for_a_level(): void
    {
        $sport = Sport::create(['name' => 'Basketball', 'slug' => 'basketball']);
        $elementaryGame = $this->makeGame($sport, Level::ELEMENTARY, 'Elem Basketball');
        $this->makeGame($sport, Level::HIGH_SCHOOL, 'HS Basketball');

        $this->get(route('public.sport', ['sport' => $sport, 'level' => Level::ELEMENTARY]))
            ->assertOk()
            ->assertSee('Elem Basketball')
            ->assertDontSee('HS Basketball')
            ->assertSee('Overall Tally')
            ->assertDontSee('Public Tally');

        $this->get(route('public.game', [$sport, $elementaryGame]))
            ->assertOk()
            ->assertSee('Overall Tally')
            ->assertDontSee('Public Tally');
    }

    public function test_admin_must_pick_a_valid_level_when_creating_a_game(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);
        $sport = Sport::create(['name' => 'Basketball', 'slug' => 'basketball']);

        $this->actingAs($admin)
            ->post(route('admin.games.store'), [
                'sport_id' => $sport->id,
                'name' => 'Invalid Level Game',
                'level' => 'college',
                'bracket_type' => 'none',
            ])
            ->assertSessionHasErrors('level');

        $this->actingAs($admin)
            ->post(route('admin.games.store'), [
                'sport_id' => $sport->id,
                'name' => 'Valid Level Game',
                'level' => Level::HIGH_SCHOOL,
                'bracket_type' => 'none',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('games', [
            'name' => 'Valid Level Game',
            'level' => Level::HIGH_SCHOOL,
        ]);
    }
}
