
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tournament_fixtures', function (Blueprint $table) {
            $table->date('played_on')->nullable()->index();
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreignId('shuttlecock_player_id')
                ->nullable()
                ->constrained('players')
                ->restrictOnDelete();

            $table->unsignedInteger('shuttlecock_count')->nullable();

            $table->boolean('shuttlecock_review_required')
                ->default(false);

            $table->unique(
                ['transaction_date', 'shuttlecock_player_id'],
                'cash_cock_player_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropUnique('cash_cock_player_date_unique');
            $table->dropConstrainedForeignId('shuttlecock_player_id');
            $table->dropColumn([
                'shuttlecock_count',
                'shuttlecock_review_required',
            ]);
        });

        Schema::table('tournament_fixtures', function (Blueprint $table) {
            $table->dropIndex(['played_on']);
            $table->dropColumn('played_on');
        });
    }
};
