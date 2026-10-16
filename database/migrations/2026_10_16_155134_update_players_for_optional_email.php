
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');

            $table->string('email')
                ->nullable()
                ->unique()
                ->after('name');

            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->change();
        });

        Schema::table('players', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });

        $players = DB::table('players')
            ->select('id', 'user_id')
            ->get();

        foreach ($players as $player) {
            $user = $player->user_id
                ? DB::table('users')
                    ->where('id', $player->user_id)
                    ->first()
                : null;

            DB::table('players')
                ->where('id', $player->id)
                ->update([
                    'name' => $user?->name
                        ?? 'Pemain ' . $player->id,
                    'email' => $user?->email,
                ]);
        }

        Schema::table('players', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        if (DB::table('players')->whereNull('user_id')->exists()) {
            throw new RuntimeException(
                'Rollback tidak aman: ada pemain tanpa akun.'
            );
        }

        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')
                ->nullable(false)
                ->change();

            $table->dropUnique(['email']);
            $table->dropColumn(['name', 'email']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};
