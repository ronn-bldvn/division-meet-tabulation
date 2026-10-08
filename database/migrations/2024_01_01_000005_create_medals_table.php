<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['gold', 'silver', 'bronze']);
            $table->string('result_image')->nullable();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamps();
            $table->unique(['game_id', 'type']);
        });
    }
    public function down(): void { Schema::dropIfExists('medals'); }
};
