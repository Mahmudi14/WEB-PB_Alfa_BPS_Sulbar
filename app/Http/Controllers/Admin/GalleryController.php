<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GalleryController extends Controller
{
    public function index()
    {
        $images = GalleryImage::query()
            ->latest('id')
            ->get();

        return view('admin.gallery.index', compact('images'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=3000,max_height=3000',
            ],
            'caption' => [
                'nullable',
                'string',
                'max:150',
            ],
        ]);

        $path = null;

        try {
            DB::transaction(function () use ($request, $data, &$path) {

                // Kunci baris pengaturan agar batas lima foto
                // tetap konsisten saat ada upload bersamaan.
                SiteSetting::whereKey(1)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (GalleryImage::count() >= 6) {
                    throw ValidationException::withMessages([
                        'image' => 'Maksimal 6 foto. Hapus foto lama terlebih dahulu.',
                    ]);
                }

                $path = $request->file('image')
                    ->store('gallery', 'public');

                if (!$path) {
                    throw ValidationException::withMessages([
                        'image' => 'Foto gagal disimpan.',
                    ]);
                }

                GalleryImage::create([
                    'image_path' => $path,
                    'caption' => $data['caption'] ?? null,
                    'created_by' => $request->user()->id,
                ]);

            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }

        return redirect()
            ->route('admin.gallery.index')
            ->with('success', 'Foto berhasil ditambahkan.');
    }

    public function destroy(GalleryImage $image)
    {
        $path = $image->image_path;

        $image->delete();

        Storage::disk('public')->delete($path);

        return redirect()
            ->route('admin.gallery.index')
            ->with('success', 'Foto berhasil dihapus.');
    }
}
