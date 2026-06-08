<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('champion_pick_team_id')->nullable()->after('role');
            $table->boolean('champion_points_awarded')->default(false)->after('champion_pick_team_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['champion_pick_team_id', 'champion_points_awarded']);
        });
    }
};
