<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class GalleryController extends Controller
{
    private const MAX_IMAGES = 6;

    public function index(): View
    {
        $images = GalleryImage::query()
            ->with('creator:id,name')
            ->latest('id')
            ->get();

        $maxImages = self::MAX_IMAGES;

        $remainingSlots = max(
            0,
            $maxImages - $images->count()
        );

        return view('admin.gallery.index', compact(
            'images',
            'maxImages',
            'remainingSlots'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'caption' => $this->cleanCaption(
                $request->input('caption')
            ),
        ]);

        $data = $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:max_width=4000,max_height=4000',
            ],
            'caption' => [
                'nullable',
                'string',
                'max:150',
            ],
        ], [
            'image.required' => 'Pilih foto terlebih dahulu.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' =>
            'Format foto harus JPG, PNG, atau WebP.',
            'image.max' =>
            'Ukuran maksimal foto adalah 5 MB.',
            'image.dimensions' =>
            'Dimensi maksimal adalah 4000 × 4000 piksel.',
            'caption.max' =>
            'Keterangan maksimal 150 karakter.',
        ]);

        $storedPath = null;

        try {
            DB::transaction(function () use (
                $request,
                $data,
                &$storedPath
            ) {
                $setting = SiteSetting::query()
                    ->whereKey(1)
                    ->lockForUpdate()
                    ->first();

                abort_unless(
                    $setting,
                    500,
                    'Pengaturan website tidak ditemukan.'
                );

                if (
                    GalleryImage::query()->count()
                    >= self::MAX_IMAGES
                ) {
                    throw ValidationException::withMessages([
                        'image' =>
                        'Galeri sudah penuh. Hapus salah satu foto terlebih dahulu.',
                    ]);
                }

                $storedPath = $request->file('image')
                    ->store('gallery', 'public');

                if (!$storedPath) {
                    throw new RuntimeException(
                        'Foto gagal disimpan ke storage.'
                    );
                }

                GalleryImage::query()->create([
                    'image_path' => $storedPath,
                    'caption' => $data['caption'] ?? null,
                    'created_by' => $request->user()->id,
                ]);
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.gallery.index')
            ->with(
                'success',
                'Foto berhasil ditambahkan ke Galeri Klub.'
            );
    }

    public function updateCaption(
        Request $request,
        GalleryImage $image
    ): RedirectResponse {
        $request->merge([
            'caption' => $this->cleanCaption(
                $request->input('caption')
            ),
        ]);

        $data = $request->validate([
            'caption' => [
                'nullable',
                'string',
                'max:150',
            ],
        ]);

        $image->update([
            'caption' => $data['caption'] ?? null,
        ]);

        return redirect()
            ->route('admin.gallery.index')
            ->with(
                'success',
                'Keterangan foto berhasil diperbarui.'
            );
    }

    public function destroy(
        GalleryImage $image
    ): RedirectResponse {
        $path = DB::transaction(function () use ($image) {
            $locked = GalleryImage::query()
                ->whereKey($image->id)
                ->lockForUpdate()
                ->firstOrFail();

            $path = $locked->image_path;

            $locked->delete();

            return $path;
        });

        if (
            is_string($path)
            && str_starts_with($path, 'gallery/')
        ) {
            Storage::disk('public')->delete($path);
        }

        return redirect()
            ->route('admin.gallery.index')
            ->with(
                'success',
                'Foto berhasil dihapus dari Galeri Klub.'
            );
    }

    private function cleanCaption(mixed $caption): mixed
    {
        if (!is_string($caption)) {
            return $caption;
        }

        $caption = trim($caption);

        return $caption === '' ? null : $caption;
    }
}
