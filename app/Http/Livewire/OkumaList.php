<?php

namespace App\Http\Livewire;

use App\Models\Bina;
use App\Models\Bedel;
use App\Models\Okuma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OkumaList extends Component
{
    public $bina;
    public $sakinler;
    public $okumali_bedeller;

    public $readings = false;
    public $show_modal = false;

    protected $listeners = [
        'delete' => 'deleteAttach',
        'getReadings' => 'getReadings',
    ];

    public function mount()
    {
        if (!session('bina_id')) {
            return redirect('/bina-list');
        }

        $this->bina = Bina::query()
            ->accessibleTo(Auth::user())
            ->findOrFail(session('bina_id'));
        $this->sakinler = $this->bina->sakinler;
        $this->okumali_bedeller = Bedel::query()
            ->where('bina_id', '=', session('bina_id'))
            ->where('tur', '=', 'SAYAC')
            ->get();
    }

    public function render(Request $req)
    {
        // $q = $this->getSakinler($req);

        return view('kayit.okumalar-gor');
    }

    public function getReadings(Request $request, $idSakin, $idBedel)
    {
        $this->readings = Okuma::query()
            ->where('bina_id', '=', session('bina_id'))
            ->where('sakin_id', '=', $idSakin)
            ->where('bedel_id', '=', $idBedel)
            ->orderBy('id', 'desc')
            ->get();

        //$this->show_modal = !$this->show_modal;

        // dd($this->readings);
    }

    // public function getSakinler($req)
    // {
    //     $q = Sakin::query()->orderBy(
    //         $this->sortTimeField,
    //         $this->sortTimeDirection
    //     );

    //     $q->where('bina_id', '=', $req->id);

    //     if (strlen($this->search) > 0) {
    //         $q->where('name', 'like', '%' . $this->search . '%');
    //         $q->orWhere('lastname', 'like', '%' . $this->search . '%');
    //     }

    //     return $q;
    // }
}
