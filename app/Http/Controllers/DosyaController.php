<?php

namespace App\Http\Controllers;

use App\Models\Dosya;
use App\Models\Bina;
use App\Models\Kayit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DosyaController extends Controller
{
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
