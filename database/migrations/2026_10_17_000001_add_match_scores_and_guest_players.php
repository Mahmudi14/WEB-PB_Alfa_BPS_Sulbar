<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->unsignedTinyInteger('set1_a')->nullable();
            $table->unsignedTinyInteger('set1_b')->nullable();
            $table->unsignedTinyInteger('set2_a')->nullable();
            $table->unsignedTinyInteger('set2_b')->nullable();
        });

        Schema::table('match_participants', function (Blueprint $table) {
            $table->foreignId('player_id')->nullable()->change();
            $table->string('guest_name', 120)->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('match_participants')
            ->whereNull('player_id')
            ->exists()) {
            throw new RuntimeException(
                'Masih ada Pemain Luar. Rollback dibatalkan.'
            );
        }

        Schema::table('match_participants', function (Blueprint $table) {
            $table->dropColumn('guest_name');
            $table->foreignId('player_id')->nullable(false)->change();
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn([
                'set1_a',
                'set1_b',
                'set2_a',
                'set2_b',
            ]);
        });
    }
};