
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            $table->string('logo_path')->nullable();

            $table->string('about_title', 120)
                ->default('Tentang Kami');

            $table->text('about_text');

            $table->timestamps();
        });

        // Satu baris pengaturan untuk seluruh website.
        DB::table('site_settings')->insert([
            'id' => 1,
            'logo_path' => null,
            'about_title' => 'Tentang Kami',
            'about_text' =>
                'PB Alfa BPS Sulawesi Barat merupakan ' .
                'komunitas bulu tangkis yang menjadi ' .
                'wadah olahraga, kebersamaan, dan sportivitas.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
