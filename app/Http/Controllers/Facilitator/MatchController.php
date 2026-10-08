<?php

namespace App\Http\Controllers\Facilitator;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameMatch;
use App\Models\Medal;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MatchController extends Controller
{
    private function authorizeGame(Game $game): void
    {
        abort_unless($game->sport_id === Auth::user()->sport_id, 403);
    }

    /** Bracket management screen: elimination / semifinal / final columns. */
    public function index(Game $game)
    {
        $this->authorizeGame($game);

        $game->load(['matches.team1', 'matches.team2', 'matches.winner', 'matches.winnerNextMatch', 'matches.loserNextMatch']);
        $teams = Team::forLevel($game->level)->orderBy('name')->get();
        $roundOptions = $this->roundOptions($game);
        $bracket = $game->matches->groupBy('round');

        return view('facilitator.matches', compact('game', 'bracket', 'teams', 'roundOptions'));
    }

    public function updateBracket(Request $request, Game $game)
    {
        $this->authorizeGame($game);

        $data = $request->validate([
            'bracket_type' => 'required|in:single_elimination,double_elimination,round_robin,none',
        ]);

        if ($data['bracket_type'] !== $game->bracket_type
            && ($game->matches()->exists() || $game->medals()->exists())) {
            throw ValidationException::withMessages([
                'bracket_type' => 'Remove existing matches and medals before changing the bracket format.',
            ]);
        }

        $game->update(['bracket_type' => $data['bracket_type']]);

        return back()->with('status', 'Bracket format updated.');
    }

    /** Create a new match slot within a round. */
    public function store(Request $request, Game $game)
    {
        $this->authorizeGame($game);

        if ($game->bracket_type === 'none') {
            return back()->withErrors(['round' => 'This game format does not use matches.']);
        }

        $data = $request->validate([
            'round' => ['required', Rule::in(array_keys($this->roundOptions($game)))],
            'match_number' => 'required|integer|min:1',
            'team1_id' => 'nullable|exists:teams,id',
            'team2_id' => 'nullable|exists:teams,id',
            'scheduled_at' => 'nullable|date',
            'winner_next_match_id' => ['nullable', Rule::exists('game_matches', 'id')->where('game_id', $game->id)],
            'winner_next_slot' => 'nullable|in:team1,team2',
            'loser_next_match_id' => ['nullable', Rule::exists('game_matches', 'id')->where('game_id', $game->id)],
            'loser_next_slot' => 'nullable|in:team1,team2',
        ]);

        $this->validateTeamsForLevel($data, $game);
        $this->validateDistinctTeams($data);
        $this->validateRouteSlots($data);
        $this->validateUniqueRouteSlots($data, $game);

        if ($game->bracket_type === 'single_elimination' && in_array($data['round'], ['semifinal', 'final'], true)) {
            $previousRound = $data['round'] === 'semifinal' ? 'elimination' : 'semifinal';
            $eligible = $game->matches()
                ->where('round', $previousRound)
                ->where('status', 'completed')
                ->whereNotNull('winner_id')
                ->pluck('winner_id');
            foreach (['team1_id', 'team2_id'] as $teamField) {
                if (! empty($data[$teamField]) && ! $eligible->contains($data[$teamField])) {
                    throw ValidationException::withMessages([$teamField => 'Only winners from the previous round can advance.']);
                }
            }
        }

        $this->assertTargetsAreDifferentMatches($data);
        $data['game_id'] = $game->id;

        $match = GameMatch::create($data);
        $this->syncDoubleRoutes($game, $before, $match);

        return back()->with('status', 'Match added to the bracket.');
    }

    /** Facilitator enters the score/result for a match, optionally with a result photo. */
    public function update(Request $request, Game $game, GameMatch $match)
    {
        $this->authorizeGame($game);
        abort_unless($match->game_id === $game->id, 404);

        $before = $match->fresh();
        $data = $request->validate([
            'round' => ['sometimes', 'required', Rule::in(array_keys($this->roundOptions($game)))],
            'match_number' => 'sometimes|required|integer|min:1',
            'team1_id' => 'sometimes|nullable|exists:teams,id',
            'team2_id' => 'sometimes|nullable|exists:teams,id',
            'team1_score' => 'sometimes|nullable|integer|min:0',
            'team2_score' => 'sometimes|nullable|integer|min:0',
            'winner_id' => 'sometimes|nullable|exists:teams,id',
            'status' => 'required|in:scheduled,ongoing,completed',
            'result_image' => 'sometimes|nullable|image|max:5120', // 5MB
            'winner_next_match_id' => ['sometimes', 'nullable', Rule::exists('game_matches', 'id')->where('game_id', $game->id)],
            'winner_next_slot' => 'sometimes|nullable|in:team1,team2',
            'loser_next_match_id' => ['sometimes', 'nullable', Rule::exists('game_matches', 'id')->where('game_id', $game->id)],
            'loser_next_slot' => 'sometimes|nullable|in:team1,team2',
        ]);

        $effective = array_merge($match->only([
            'round', 'match_number', 'team1_id', 'team2_id', 'team1_score', 'team2_score',
            'winner_id', 'winner_next_match_id', 'winner_next_slot', 'loser_next_match_id', 'loser_next_slot',
        ]), $data);
        $participantsChanged = (int) ($effective['team1_id'] ?? 0) !== (int) ($before->team1_id ?? 0)
            || (int) ($effective['team2_id'] ?? 0) !== (int) ($before->team2_id ?? 0);
        if ($participantsChanged) {
            $data = array_merge($data, [
                'winner_id' => null,
                'team1_score' => null,
                'team2_score' => null,
                'status' => 'scheduled',
            ]);
            $effective = array_merge($effective, $data);
        }

        $this->validateTeamsForLevel($effective, $game);
        $this->validateDistinctTeams($effective);
        $this->validateRouteSlots($effective);
        $this->validateUniqueRouteSlots($effective, $game, $match->id);
        $this->assertTargetsAreDifferentMatches($effective, $match->id);
        $this->validateNoRouteCycles($effective, $game, $match->id);

        if ($participantsChanged && $match->result_image) {
            Storage::disk('public')->delete($match->result_image);
            $data['result_image'] = null;
        }

        if ($request->hasFile('result_image')) {
            $data['result_image'] = $request->file('result_image')->store('results/matches', 'public');
        }

        $team1Id = $effective['team1_id'];
        $team2Id = $effective['team2_id'];
        $winnerId = $effective['winner_id'];
        if ($winnerId && ! in_array($winnerId, [$team1Id, $team2Id])) {
            throw ValidationException::withMessages(['winner_id' => 'The winner must be one of the teams in this match.']);
        }
        if ($data['status'] === 'completed' && (! $winnerId || ! $team1Id || ! $team2Id)) {
            throw ValidationException::withMessages(['status' => 'A completed match needs two teams and a winner.']);
        }

        $match->update($data);

        $match = $match->fresh();
        if ($game->bracket_type === 'single_elimination') {
            $this->advanceWinner($game, $match);
        } elseif ($game->bracket_type === 'double_elimination') {
            $this->syncDoubleRoutes($game, $before, $match);
        }
        $medalMessage = $this->updateAutomaticMedals($game);

        return back()->with(
            'status',
            $medalMessage ?? ($participantsChanged
                ? 'Match teams updated; the previous result was cleared. Enter the new result to advance teams.'
                : 'Match result saved.')
        );
    }

    private function advanceWinner(Game $game, GameMatch $match): void
    {
        if ($game->bracket_type !== 'single_elimination') {
            return;
        }

        $nextRound = ['elimination' => 'semifinal', 'semifinal' => 'final'][$match->round] ?? null;
        if ($nextRound === null) {
            return;
        }

        $nextMatchNumber = intdiv($match->match_number + 1, 2);
        $next = GameMatch::firstOrNew([
            'game_id' => $game->id,
            'round' => $nextRound,
            'match_number' => $nextMatchNumber,
        ]);
        if (! $next->exists && $match->status !== 'completed') {
            return;
        }
        if (! $next->exists) {
            $next->save();
        }

        $feederMatches = GameMatch::where('game_id', $game->id)
            ->where('round', $match->round)
            ->whereBetween('match_number', [$next->match_number * 2 - 1, $next->match_number * 2])
            ->where('status', 'completed')
            ->whereNotNull('winner_id')
            ->get()
            ->keyBy('match_number');

        $team1Id = $feederMatches->get($next->match_number * 2 - 1)?->winner_id;
        $team2Id = $feederMatches->get($next->match_number * 2)?->winner_id;
        $teamsChanged = $next->team1_id !== $team1Id || $next->team2_id !== $team2Id;
        $next->team1_id = $team1Id;
        $next->team2_id = $team2Id;

        if ($teamsChanged) {
            $next->team1_score = null;
            $next->team2_score = null;
            $next->winner_id = null;
            $next->status = 'scheduled';
            $next->save();
            $this->advanceWinner($game, $next);
        } else {
            $next->save();
        }
    }

    private function updateAutomaticMedals(Game $game): ?string
    {
        if ($game->bracket_type === 'single_elimination') {
            $final = $game->matches()->where('round', 'final')->where('status', 'completed')->latest('id')->first();
            if (! $final || ! $final->winner_id || ! $final->team1_id || ! $final->team2_id) {
                $this->clearAutomaticMedals($game);

                return null;
            }

            $silverId = $final->winner_id === $final->team1_id ? $final->team2_id : $final->team1_id;
            $thirdPlace = $game->matches()->where('round', 'third_place')->first();
            $bronzeId = $thirdPlace?->status === 'completed' ? $thirdPlace->winner_id : null;
            if (! $thirdPlace) {
                $bronzeId = $game->matches()
                    ->where('round', 'semifinal')
                    ->where('status', 'completed')
                    ->whereNotNull('winner_id')
                    ->get()
                    ->map(fn ($match) => $match->winner_id === $match->team1_id ? $match->team2_id : $match->team1_id)
                    ->filter()
                    ->unique()
                    ->first();
            }

            $this->saveAutomaticMedals($game, [
                'gold' => $final->winner_id,
                'silver' => $silverId,
                'bronze' => $bronzeId,
            ], true);

            return null;
        }

        if ($game->bracket_type === 'double_elimination') {
            $this->updateDoubleEliminationMedals($game);

            return null;
        }

        if ($game->bracket_type === 'round_robin') {
            return $this->updateRoundRobinMedals($game);
        }

        return null;
    }

    private function updateDoubleEliminationMedals(Game $game): void
    {
        $final = $game->matches()->where('round', 'grand_final')->where('status', 'completed')->latest('id')->first();
        if (! $final || ! $final->winner_id || ! $final->team1_id || ! $final->team2_id) {
            $this->clearAutomaticMedals($game);

            return;
        }

        $finalLoserId = $final->winner_id === $final->team1_id ? $final->team2_id : $final->team1_id;
        $loserHadPreviousLoss = $game->matches()
            ->where('id', '!=', $final->id)
            ->whereNotIn('round', ['grand_final', 'grand_final_reset'])
            ->where('status', 'completed')
            ->whereNotNull('winner_id')
            ->where(function ($query) use ($finalLoserId) {
                $query->where(function ($query) use ($finalLoserId) {
                    $query->where('team1_id', $finalLoserId)->whereColumn('winner_id', '!=', 'team1_id');
                })->orWhere(function ($query) use ($finalLoserId) {
                    $query->where('team2_id', $finalLoserId)->whereColumn('winner_id', '!=', 'team2_id');
                });
            })
            ->exists();

        if (! $loserHadPreviousLoss) {
            $reset = GameMatch::firstOrCreate(
                ['game_id' => $game->id, 'round' => 'grand_final_reset', 'match_number' => 1],
                ['team1_id' => $final->team1_id, 'team2_id' => $final->team2_id],
            );
            if ($reset->team1_id !== $final->team1_id || $reset->team2_id !== $final->team2_id) {
                $reset->update([
                    'team1_id' => $final->team1_id,
                    'team2_id' => $final->team2_id,
                    'winner_id' => null,
                    'status' => 'scheduled',
                    'team1_score' => null,
                    'team2_score' => null,
                ]);
            }
            if ($reset->status !== 'completed' || ! $reset->winner_id) {
                $this->clearAutomaticMedals($game);

                return;
            }

            $final = $reset;
        } else {
            $game->matches()->where('round', 'grand_final_reset')->delete();
        }

        $losersFinal = $game->matches()->where('round', 'losers_final')->where('status', 'completed')->first();
        $bronzeId = $losersFinal && $losersFinal->winner_id
            ? ($losersFinal->winner_id === $losersFinal->team1_id ? $losersFinal->team2_id : $losersFinal->team1_id)
            : null;
        $silverId = $final->winner_id === $final->team1_id ? $final->team2_id : $final->team1_id;
        $this->saveAutomaticMedals($game, [
            'gold' => $final->winner_id,
            'silver' => $silverId,
            'bronze' => $bronzeId,
        ], true);
    }

    private function updateRoundRobinMedals(Game $game): ?string
    {
        $matches = $game->matches()->get();
        if ($matches->isEmpty() || $matches->contains(fn ($match) => ! $match->team1_id
            || ! $match->team2_id
            || $match->status !== 'completed'
            || ! $match->winner_id)) {
            $this->clearAutomaticMedals($game);

            return null;
        }

        $teamIds = $matches->flatMap(fn ($match) => [$match->team1_id, $match->team2_id])->unique()->values();
        $wins = $teamIds->mapWithKeys(fn ($teamId) => [$teamId => $matches->where('winner_id', $teamId)->count()]);
        $awards = [];
        $unresolvedTie = false;

        foreach (['gold', 'silver', 'bronze'] as $type) {
            if ($wins->isEmpty()) {
                break;
            }

            $mostWins = $wins->max();
            $tiedIds = $wins->filter(fn ($count) => $count === $mostWins)->keys();
            if ($tiedIds->count() > 1) {
                $headToHeadWins = $tiedIds->mapWithKeys(fn ($teamId) => [
                    $teamId => $matches->filter(fn ($match) => $tiedIds->contains($match->team1_id)
                        && $tiedIds->contains($match->team2_id)
                        && $match->winner_id === $teamId)->count(),
                ]);
                $bestHeadToHead = $headToHeadWins->max();
                $tiedIds = $headToHeadWins->filter(fn ($count) => $count === $bestHeadToHead)->keys();
            }

            if ($tiedIds->count() !== 1) {
                $unresolvedTie = true;
                break;
            }

            $winnerId = $tiedIds->first();
            $awards[$type] = $winnerId;
            $wins->forget($winnerId);
        }

        $this->saveAutomaticMedals($game, $awards, $awards && count($awards) >= min(3, $teamIds->count()));

        return $unresolvedTie
            ? 'Matches are complete, but head-to-head results do not resolve a tie. Medals for tied places remain unassigned.'
            : null;
    }

    private function saveAutomaticMedals(Game $game, array $awards, bool $completed = false): void
    {
        foreach (['gold', 'silver', 'bronze'] as $type) {
            if (isset($awards[$type])) {
                Medal::updateOrCreate(
                    ['game_id' => $game->id, 'type' => $type],
                    ['team_id' => $awards[$type], 'awarded_at' => now()],
                );
            } else {
                Medal::where('game_id', $game->id)->where('type', $type)->delete();
            }
        }

        $game->update(['status' => $completed ? 'completed' : 'ongoing']);
    }

    private function clearAutomaticMedals(Game $game): void
    {
        Medal::where('game_id', $game->id)->delete();
        $game->update(['status' => 'ongoing']);
    }

    private function syncDoubleRoutes(Game $game, GameMatch $before, GameMatch $after, array &$visited = []): void
    {
        if (isset($visited[$after->id])) {
            return;
        }
        $visited[$after->id] = true;

        foreach (['winner', 'loser'] as $kind) {
            $idField = $kind.'_next_match_id';
            $slotField = $kind.'_next_slot';
            $oldTeamId = $this->advancingTeamId($before, $kind);
            $newTeamId = $this->advancingTeamId($after, $kind);
            $oldTargetId = $before->{$idField};
            $newTargetId = $after->{$idField};
            $oldSlot = $before->{$slotField};
            $newSlot = $after->{$slotField};

            if ($oldTargetId && $oldSlot && $oldTeamId
                && ($oldTargetId !== $newTargetId || $oldSlot !== $newSlot || $oldTeamId !== $newTeamId)) {
                $this->setRoutedTeam($game, $oldTargetId, $oldSlot, null, $visited);
            }

            if ($newTargetId && $newSlot && $newTeamId) {
                $this->setRoutedTeam($game, $newTargetId, $newSlot, $newTeamId, $visited);
            }
        }
    }

    private function advancingTeamId(GameMatch $match, string $kind): ?int
    {
        if ($match->status !== 'completed' || ! $match->winner_id) {
            return null;
        }

        if ($kind === 'winner') {
            return $match->winner_id;
        }

        if ($match->winner_id === $match->team1_id) {
            return $match->team2_id;
        }

        return $match->team1_id;
    }

    private function setRoutedTeam(Game $game, int $targetId, string $slot, ?int $teamId, array &$visited): void
    {
        $target = GameMatch::where('game_id', $game->id)->findOrFail($targetId);
        $field = $slot.'_id';
        if ($target->{$field} === $teamId) {
            return;
        }

        $before = $target->fresh();
        $target->update([
            $field => $teamId,
            'team1_score' => null,
            'team2_score' => null,
            'winner_id' => null,
            'status' => 'scheduled',
        ]);
        $this->syncDoubleRoutes($game, $before, $target->fresh(), $visited);
    }

    private function roundOptions(Game $game): array
    {
        if ($game->bracket_type === 'round_robin') {
            return ['round_robin' => 'Round Robin'];
        }

        if ($game->bracket_type === 'double_elimination') {
            $rounds = [];
            for ($round = 1; $round <= 6; $round++) {
                $rounds['winners_round_'.$round] = "Winners' Round $round";
            }
            for ($round = 1; $round <= 6; $round++) {
                $rounds['losers_round_'.$round] = "Losers' Round $round";
            }

            return $rounds + [
                'losers_final' => "Losers' Final",
                'grand_final' => 'Grand Final',
                'grand_final_reset' => 'Grand Final Reset',
                'third_place' => 'Third Place',
            ];
        }

        return [
            'elimination' => 'Elimination',
            'semifinal' => 'Semifinal',
            'final' => 'Final',
            'third_place' => 'Third Place',
        ];
    }

    private function validateTeamsForLevel(array $data, Game $game): void
    {
        foreach (['team1_id', 'team2_id'] as $field) {
            if (! empty($data[$field]) && ! Team::forLevel($game->level)->whereKey($data[$field])->exists()) {
                throw ValidationException::withMessages([$field => 'Choose a team assigned to the game level.']);
            }
        }
    }

    private function validateDistinctTeams(array $data): void
    {
        if (! empty($data['team1_id']) && $data['team1_id'] === ($data['team2_id'] ?? null)) {
            throw ValidationException::withMessages(['team2_id' => 'A team cannot play against itself.']);
        }
    }

    private function validateRouteSlots(array $data): void
    {
        foreach (['winner', 'loser'] as $kind) {
            if (! empty($data[$kind.'_next_match_id']) !== ! empty($data[$kind.'_next_slot'])) {
                throw ValidationException::withMessages([
                    $kind.'_next_match_id' => 'Select both a destination match and its team slot.',
                ]);
            }
        }
    }

    private function validateUniqueRouteSlots(array $data, Game $game, ?int $matchId = null): void
    {
        foreach (['winner', 'loser'] as $kind) {
            $targetId = $data[$kind.'_next_match_id'] ?? null;
            $slot = $data[$kind.'_next_slot'] ?? null;
            if (! $targetId || ! $slot) {
                continue;
            }

            $alreadyUsed = GameMatch::where('game_id', $game->id)
                ->when($matchId, fn ($query) => $query->where('id', '!=', $matchId))
                ->where(function ($query) use ($targetId, $slot) {
                    $query->where(function ($query) use ($targetId, $slot) {
                        $query->where('winner_next_match_id', $targetId)->where('winner_next_slot', $slot);
                    })->orWhere(function ($query) use ($targetId, $slot) {
                        $query->where('loser_next_match_id', $targetId)->where('loser_next_slot', $slot);
                    });
                })
                ->exists();

            if ($alreadyUsed) {
                throw ValidationException::withMessages([
                    $kind.'_next_slot' => 'That match slot already has an advancing team assigned.',
                ]);
            }
        }
    }

    private function validateNoRouteCycles(array $data, Game $game, int $matchId): void
    {
        foreach (['winner_next_match_id', 'loser_next_match_id'] as $field) {
            $targetId = $data[$field] ?? null;
            if (! $targetId) {
                continue;
            }

            $pending = [(int) $targetId];
            $visited = [];
            while ($pending !== []) {
                $currentId = array_pop($pending);
                if ($currentId === $matchId) {
                    throw ValidationException::withMessages([$field => 'Bracket advancement cannot loop back to this match.']);
                }
                if (isset($visited[$currentId])) {
                    continue;
                }
                $visited[$currentId] = true;

                $nextMatch = GameMatch::where('game_id', $game->id)->find($currentId);
                if ($nextMatch) {
                    if ($nextMatch->winner_next_match_id) {
                        $pending[] = $nextMatch->winner_next_match_id;
                    }
                    if ($nextMatch->loser_next_match_id) {
                        $pending[] = $nextMatch->loser_next_match_id;
                    }
                }
            }
        }
    }

    private function assertTargetsAreDifferentMatches(array $data, ?int $matchId = null): void
    {
        foreach (['winner_next_match_id', 'loser_next_match_id'] as $field) {
            if (! empty($data[$field]) && (int) $data[$field] === $matchId) {
                throw ValidationException::withMessages([$field => 'A match cannot advance to itself.']);
            }
        }
    }

    public function destroy(Game $game, GameMatch $match)
    {
        $this->authorizeGame($game);
        abort_unless($match->game_id === $game->id, 404);

        if ($match->result_image) {
            Storage::disk('public')->delete($match->result_image);
        }

        if ($game->bracket_type === 'double_elimination') {
            $empty = $match->replicate();
            $empty->id = $match->id;
            $empty->status = 'scheduled';
            $empty->winner_id = null;
            $this->syncDoubleRoutes($game, $match, $empty);
        }

        $match->delete();
        if ($game->bracket_type === 'single_elimination') {
            $empty = $match->replicate();
            $empty->id = $match->id;
            $empty->status = 'scheduled';
            $empty->winner_id = null;
            $this->advanceWinner($game, $empty);
        }
        $this->updateAutomaticMedals($game);

        return back()->with('status', 'Match removed.');
    }
}
