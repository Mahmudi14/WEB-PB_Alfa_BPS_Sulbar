<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('format', ['liga', 'cup']);
            $table->enum('match_type', ['tunggal', 'ganda']);
            $table->enum('set_format', ['two_draw', 'two_aggregate', 'best_of_three']);
            $table->unsignedTinyInteger('target_points');
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('tournament_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->unsignedSmallInteger('seed')->default(0);
            $table->timestamps();
            $table->index(['tournament_id', 'seed']);
        });

        Schema::create('tournament_entry_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('entry_id')->constrained('tournament_entries')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->restrictOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->unique(['tournament_id', 'player_id'], 'tourney_player_once');
            $table->unique(['entry_id', 'slot'], 'tourney_entry_player_slot');
        });

        Schema::create('tournament_fixtures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->unsignedSmallInteger('round_number');
            $table->unsignedSmallInteger('match_number');
            $table->foreignId('a_entry_id')->nullable()->constrained('tournament_entries')->nullOnDelete();
            $table->foreignId('b_entry_id')->nullable()->constrained('tournament_entries')->nullOnDelete();
            $table->foreignId('winner_entry_id')->nullable()->constrained('tournament_entries')->nullOnDelete();
            $table->foreignId('next_fixture_id')->nullable()->constrained('tournament_fixtures')->nullOnDelete();
            $table->enum('next_slot', ['a', 'b'])->nullable();
            $table->enum('result', ['a', 'b', 'draw'])->nullable();
            $table->boolean('is_bye')->default(false);
            $table->unsignedSmallInteger('shuttlecock_count')->default(0);
            $table->unsignedSmallInteger('jus_a')->nullable();
            $table->unsignedSmallInteger('jus_b')->nullable();
            $table->timestamps();
            $table->unique(['tournament_id', 'round_number', 'match_number'], 'tourney_round_match_unique');
        });

        Schema::create('tournament_fixture_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained('tournament_fixtures')->cascadeOnDelete();
            $table->unsignedTinyInteger('set_number');
            $table->unsignedSmallInteger('a_score');
            $table->unsignedSmallInteger('b_score');
            $table->unique(['fixture_id', 'set_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_fixture_sets');
        Schema::dropIfExists('tournament_fixtures');
        Schema::dropIfExists('tournament_entry_players');
        Schema::dropIfExists('tournament_entries');
        Schema::dropIfExists('tournaments');
    }
};
