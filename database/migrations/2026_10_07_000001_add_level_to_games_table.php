<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Every event/game belongs to a level: Elementary or High School.
        Schema::table('games', function (Blueprint $table) {
            $table->enum('level', ['elementary', 'high_school'])
                ->default('high_school')
                ->after('category')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['level']);
            $table->dropColumn('level');
        });
    }
};
