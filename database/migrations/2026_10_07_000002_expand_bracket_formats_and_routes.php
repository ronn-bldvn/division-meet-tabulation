<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('bracket_type')->default('single_elimination')->change();
        });

        Schema::table('game_matches', function (Blueprint $table) {
            $table->string('round')->default('elimination')->change();
            $table->foreignId('winner_next_match_id')->nullable()->constrained('game_matches')->nullOnDelete();
            $table->string('winner_next_slot')->nullable();
            $table->foreignId('loser_next_match_id')->nullable()->constrained('game_matches')->nullOnDelete();
            $table->string('loser_next_slot')->nullable();
        });

        foreach (range(1, 4) as $number) {
            if (! DB::table('teams')->where('code', 'elemteam'.$number)->exists()) {
                DB::table('teams')
                    ->where('code', 'DIV'.$number)
                    ->update([
                        'name' => 'Elementary Division '.$number,
                        'code' => 'elemteam'.$number,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('game_matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_next_match_id');
            $table->dropColumn(['winner_next_slot', 'loser_next_slot']);
            $table->dropConstrainedForeignId('loser_next_match_id');
            $table->enum('round', ['elimination', 'semifinal', 'final'])->default('elimination')->change();
        });

        Schema::table('games', function (Blueprint $table) {
            $table->enum('bracket_type', ['single_elimination', 'round_robin', 'none'])->default('single_elimination')->change();
        });
    }
};
