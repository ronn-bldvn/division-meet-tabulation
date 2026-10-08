<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // "Teams" represent the competing Divisions/Delegations
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // e.g. "Division 1"
            $table->string('code', 10)->unique();  // short tag e.g. "DIV1"
            $table->string('color', 7)->default('#2563eb');
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('teams'); }
};
