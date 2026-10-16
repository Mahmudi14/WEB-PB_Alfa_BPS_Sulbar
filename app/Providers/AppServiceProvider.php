<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
        View::composer(
            ['layouts.app', 'pages.home'],
            function ($view) {

                $data = Cache::remember(
                    'pb_alfa.site_settings',
                    3600,
                    function () {

                        $settings = SiteSetting::find(1);

                        if (!$settings) {
                            return null;
                        }

                        // Simpan ARRAY, bukan model Eloquent.
                        return [
                            'logo_path' => $settings->logo_path,
                            'about_title' => $settings->about_title,
                            'about_text' => $settings->about_text,
                        ];
                    }
                );

                $siteSettings = is_array($data)
                    ? (object) $data
                    : null;

                $view->with(
                    'siteSettings',
                    $siteSettings
                );
            }
        );
    }
}
