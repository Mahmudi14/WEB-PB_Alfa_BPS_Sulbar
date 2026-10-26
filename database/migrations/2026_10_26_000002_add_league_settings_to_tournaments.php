<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->smallInteger('league_win_points')
                ->default(3);

            $table->smallInteger('league_draw_points')
                ->default(1);

            $table->smallInteger('league_loss_points')
                ->default(0);

            $table->unsignedTinyInteger('league_legs')
                ->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn([
                'league_win_points',
                'league_draw_points',
                'league_loss_points',
                'league_legs',
            ]);
        });
    }
};
