<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class SiteSettingController extends Controller
{
    private const LOGO_DIRECTORY = 'site-settings/logos';

    private const CACHE_KEY = 'pb_alfa.site_settings';

    public function edit(): View
    {
        $settings = SiteSetting::query()
            ->findOrFail(1);

        return view(
            'admin.settings.edit',
            compact('settings')
        );
    }

    public function update(Request $request): RedirectResponse
    {
        /*
         * Rapikan spasi tanpa mengubah input non-string.
         * Input yang bukan string tetap ditolak validator.
         */
        foreach (['about_title', 'about_text'] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                $request->merge([
                    $field => trim($value),
                ]);
            }
        }

        $data = $request->validate([
            'about_title' => [
                'required',
                'string',
                'max:120',
            ],
            'about_text' => [
                'required',
                'string',
                'max:10000',
            ],
            'logo' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:3072',
                'dimensions:min_width=128,min_height=128,max_width=3000,max_height=3000',
            ],
            'remove_logo' => [
                'sometimes',
                'boolean',
            ],
        ], [
            'about_title.required' =>
            'Judul Tentang Kami wajib diisi.',
            'about_title.max' =>
            'Judul maksimal 120 karakter.',
            'about_text.required' =>
            'Deskripsi Tentang Kami wajib diisi.',
            'about_text.max' =>
            'Deskripsi maksimal 10.000 karakter.',
            'logo.image' =>
            'File logo harus berupa gambar.',
            'logo.mimes' =>
            'Logo harus berformat JPG, PNG, atau WebP.',
            'logo.max' =>
            'Ukuran logo maksimal 3 MB.',
            'logo.dimensions' =>
            'Dimensi logo harus antara 128 dan 3000 piksel.',
        ]);

        $removeLogo = $request->boolean('remove_logo');
        $hasNewLogo = $request->hasFile('logo');

        if ($removeLogo && $hasNewLogo) {
            throw ValidationException::withMessages([
                'logo' =>
                'Pilih salah satu: unggah logo baru atau gunakan logo default.',
            ]);
        }

        $newLogoPath = null;
        $oldLogoPath = null;
        $logoChanged = $removeLogo || $hasNewLogo;

        /*
         * File baru disimpan dahulu.
         * Apabila transaksi database gagal,
         * file baru dibersihkan.
         */
        try {
            if ($hasNewLogo) {
                $newLogoPath = $request
                    ->file('logo')
                    ->store(
                        self::LOGO_DIRECTORY,
                        'public'
                    );

                if (
                    !is_string($newLogoPath)
                    || $newLogoPath === ''
                ) {
                    throw new RuntimeException(
                        'Logo gagal disimpan ke storage.'
                    );
                }
            }

            DB::transaction(function () use (
                $data,
                $removeLogo,
                $newLogoPath,
                &$oldLogoPath
            ) {
                $settings = SiteSetting::query()
                    ->whereKey(1)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldLogoPath = $settings->logo_path;

                $settings->about_title =
                    $data['about_title'];

                $settings->about_text =
                    $data['about_text'];

                if ($newLogoPath !== null) {
                    $settings->logo_path = $newLogoPath;
                } elseif ($removeLogo) {
                    $settings->logo_path = null;
                }

                $settings->save();
            }, 3);
        } catch (Throwable $exception) {
            if ($newLogoPath !== null) {
                Storage::disk('public')
                    ->delete($newLogoPath);
            }

            throw $exception;
        }

        /*
         * Cache pengaturan publik dan admin harus
         * dibersihkan setelah transaksi berhasil.
         */
        Cache::forget(self::CACHE_KEY);

        /*
         * Hapus file lama hanya jika:
         * 1. Logo benar-benar diganti/dihapus.
         * 2. File berasal dari direktori milik modul ini.
         *
         * Logo lama di folder lain tidak disentuh,
         * sehingga aset legacy tetap aman.
         */
        if (
            $logoChanged
            && is_string($oldLogoPath)
            && str_starts_with(
                $oldLogoPath,
                self::LOGO_DIRECTORY . '/'
            )
            && $oldLogoPath !== $newLogoPath
        ) {
            Storage::disk('public')
                ->delete($oldLogoPath);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with(
                'success',
                'Pengaturan website berhasil diperbarui.'
            );
    }
}
