<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // A "game" = one event/category under a sport, e.g. "Basketball - Men's"
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable(); // Men's, Women's, Mixed, Open...
            $table->enum('bracket_type', ['single_elimination', 'round_robin', 'none'])->default('single_elimination');
            $table->enum('status', ['upcoming', 'ongoing', 'completed'])->default('upcoming');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('games'); }
};
