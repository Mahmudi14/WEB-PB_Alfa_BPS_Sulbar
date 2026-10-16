<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SiteSettingController extends Controller
{
    public function edit()
    {
        $settings = SiteSetting::findOrFail(1);

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'about_title' => [
                'required', 'string', 'max:120',
            ],
            'about_text' => [
                'required', 'string', 'max:2000',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:min_width=32,min_height=32,max_width=2400,max_height=2400',
            ],
        ]);

        $settings = SiteSetting::findOrFail(1);

        $oldLogo = $settings->logo_path;
        $newLogo = null;

        if ($request->hasFile('logo')) {
            $newLogo = $request->file('logo')
                ->store('branding', 'public');

            if (!$newLogo) {
                throw ValidationException::withMessages([
                    'logo' => 'Gagal mengunggah logo.',
                ]);
            }
        }

        try {
            $settings->update([
                'about_title' => $data['about_title'],
                'about_text' => $data['about_text'],
                'logo_path' => $newLogo ?? $oldLogo,
            ]);
        } catch (\Throwable $e) {
            if ($newLogo) {
                Storage::disk('public')->delete($newLogo);
            }

            throw $e;
        }

        if ($newLogo && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        Cache::forget('pb_alfa.site_settings');

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Pengaturan website berhasil disimpan.');
    }

    public function deleteLogo()
    {
        $settings = SiteSetting::findOrFail(1);
        $oldLogo = $settings->logo_path;

        $settings->update([
            'logo_path' => null,
        ]);

        Cache::forget('pb_alfa.site_settings');

        if ($oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return back()->with(
            'success',
            'Logo berhasil dihapus.'
        );
    }
}
