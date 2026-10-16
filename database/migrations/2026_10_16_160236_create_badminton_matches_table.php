
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            $table->date('played_on')->index();

            $table->enum('type', [
                'tunggal',
                'ganda',
            ]);

            $table->enum('result', [
                'a',
                'b',
                'seri',
            ]);

            $table->unsignedSmallInteger('shuttlecock_count');

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['played_on', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
