<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->enum('round', ['elimination', 'semifinal', 'final'])->default('elimination');
            $table->unsignedInteger('match_number')->default(1);
            $table->foreignId('team1_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('team2_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedInteger('team1_score')->nullable();
            $table->unsignedInteger('team2_score')->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->enum('status', ['scheduled', 'ongoing', 'completed'])->default('scheduled');
            $table->string('result_image')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignId('next_match_id')->nullable()->constrained('game_matches')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('game_matches'); }
};
