<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_edit_games_teams_and_accounts(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'super_admin',
        ]);
        $sport = Sport::create(['name' => 'Basketball', 'slug' => 'basketball']);
        $otherSport = Sport::create(['name' => 'Volleyball', 'slug' => 'volleyball']);
        $game = Game::create([
            'sport_id' => $sport->id,
            'name' => 'Boys',
            'category' => 'Boys',
            'level' => 'elementary',
            'bracket_type' => 'single_elimination',
        ]);
        $team = Team::create([
            'name' => 'Division 1',
            'code' => 'DIV1',
            'color' => '#2563eb',
        ]);
        $facilitator = User::create([
            'name' => 'Facilitator',
            'email' => 'facilitator@example.com',
            'password' => 'password',
            'role' => 'facilitator',
            'sport_id' => $sport->id,
        ]);

        $this->actingAs($admin)->put(route('admin.games.update', $game), [
            'sport_id' => $otherSport->id,
            'name' => 'Girls',
            'category' => 'Girls',
            'level' => 'high_school',
            'bracket_type' => 'round_robin',
            'status' => 'ongoing',
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('admin.teams.update', $team), [
            'name' => 'Division 2',
            'code' => 'DIV2',
            'color' => '#16a34a',
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('admin.users.update', $facilitator), [
            'name' => 'Updated Facilitator',
            'email' => 'updated@example.com',
            'password' => '',
            'role' => 'super_admin',
            'sport_id' => $sport->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'sport_id' => $otherSport->id,
            'name' => 'Girls',
            'level' => 'high_school',
            'bracket_type' => 'round_robin',
            'status' => 'ongoing',
        ]);
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Division 2',
            'code' => 'DIV2',
            'color' => '#16a34a',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $facilitator->id,
            'name' => 'Updated Facilitator',
            'email' => 'updated@example.com',
            'role' => 'super_admin',
            'sport_id' => null,
        ]);

        $this->get(route('admin.games.index'))->assertOk()->assertSee('Save changes')->assertSee('Double Elimination');
        $this->get(route('admin.teams.index'))->assertOk()->assertSee('Save changes')->assertSee('elemteam1');
        $this->get(route('admin.users.index'))->assertOk()->assertSee('Save changes');
    }

    public function test_admin_dashboard_shows_a_top_five_for_each_level(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'dashboard-admin@example.com',
            'password' => 'password',
            'role' => 'super_admin',
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Top 5 — Elementary Medal Tally')
            ->assertSee('Top 5 — High School Medal Tally');
    }
}
