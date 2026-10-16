
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('match_id')
                ->constrained('matches')
                ->cascadeOnDelete();

            $table->foreignId('player_id')
                ->constrained('players')
                ->restrictOnDelete();

            $table->enum('team', ['a', 'b']);
            $table->unsignedTinyInteger('slot');

            $table->timestamps();

            $table->unique(['match_id', 'player_id']);
            $table->unique(['match_id', 'team', 'slot']);

            $table->index('player_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_participants');
    }
};
