<?php

namespace App\Http\Controllers;

use App\Models\Dosya;
use App\Models\Bina;
use App\Models\Kayit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DosyaController extends Controller
{
    public function deleteMedia(int $mediaId)
    {
        $media = Media::query()
            ->whereIn('collection_name', ['alacak-attachments', 'income-attachments', 'expense-attachments', 'payable-attachments'])
            ->findOrFail($mediaId);
        $kayit = $media->model;
        $collection = $kayit instanceof Kayit ? match ($kayit->tur) {
            'alacak' => 'alacak-attachments',
            'gelir' => 'income-attachments',
            'gider' => 'expense-attachments',
            'verecek' => 'payable-attachments',
            default => null,
        } : null;

        abort_unless(
            $collection === $media->collection_name
                && Bina::query()->accessibleTo(Auth::user())->whereKey($kayit->bina_id)->exists(),
            404,
        );

        $media->delete();

        return back();
    }

    public function deleteLegacyRecordFile(int $kayitId, int $dosyaId)
    {
        $kayit = Kayit::query()
            ->whereIn('tur', ['alacak', 'gelir', 'gider', 'verecek'])
            ->findOrFail($kayitId);

        abort_unless(
            Bina::query()->accessibleTo(Auth::user())->whereKey($kayit->bina_id)->exists(),
            404,
        );

        $file = $kayit->dosyalar()->findOrFail($dosyaId);
        Storage::disk('local')->delete($file->stored_as);
        $file->delete();

        return back();
    }

    public function media(int $kayitId, int $mediaId)
    {
        $kayit = Kayit::query()->findOrFail($kayitId);

        $collection = match ($kayit->tur) {
            'alacak' => 'alacak-attachments',
            'gelir' => 'income-attachments',
            'gider' => 'expense-attachments',
            'verecek' => 'payable-attachments',
            default => null,
        };

        abort_unless($collection !== null, 404);

        abort_unless(Bina::query()->accessibleTo(Auth::user())->whereKey($kayit->bina_id)->exists(), 404);

        $media = $kayit->getMedia($collection)->firstWhere('id', $mediaId);
        abort_unless($media && is_file($media->getPath()), 404);

        return response()->download($media->getPath(), $media->file_name);
    }

    public function dosya()
    {
        $d = Dosya::find(request('id'));

        if (!$this->checkPermission($d->kayit_id)) {
            abort(404, 'No permission!');
        }

        $dosya = Storage::path($d->stored_as);

        if (file_exists($dosya)) {
            $headers = [
                'Content-Type' => $d->mimetype,
            ];

            return response()->download(
                $dosya,
                $d->filename,
                $headers,
                'inline'
            );
        } else {
            abort(404, 'File not found!');
        }
    }

    public function checkPermission($kayit_id)
    {
        $record = Kayit::findOrFail($kayit_id);

        return Bina::query()
            ->accessibleTo(Auth::user())
            ->whereKey($record->bina_id)
            ->exists();
    }
}
