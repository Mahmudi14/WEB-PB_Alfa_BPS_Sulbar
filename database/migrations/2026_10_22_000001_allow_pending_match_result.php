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
            $table->enum('result', [
                'a',
                'b',
                'seri',
            ])->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('matches')->whereNull('result')->exists()) {
            throw new RuntimeException(
                'Masih ada pertandingan tanpa hasil.'
            );
        }

        Schema::table('matches', function (Blueprint $table) {
            $table->enum('result', [
                'a',
                'b',
                'seri',
            ])->nullable(false)->change();
        });
    }
};