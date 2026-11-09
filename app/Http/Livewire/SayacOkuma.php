<?php

namespace App\Http\Livewire;

use App\Models\Okuma;
use App\Models\User;
use App\Models\Bedel;
use App\Models\Kayit;

use Livewire\Component;
// use Livewire\Attributes\Rule;


class SayacOkuma extends Component
{

    public $current_bina_id;
    public $current_bina_name;
    public $action;
    public $sakinler;
    public $okumalar = [];

    public $okumaModal = false;
    public $bedelModal = false;

    public $okumaBedeli = false;
    public $okumaNotu;


    public $currentSakin ;
    public $okuma = false;

    public $idOkuma = false;
    public $okumaTarihi = null;


    public $okumaDegeri = null;

    public $sayacliOkumaTurleri = [];
    public $activeSayacTab;
    public $bedel_id;



    public $bedel = [
        'user_id' => '',
        'bina_id' => '',
        'sakin_id' => '',
        'tur' => '',
        'aciklama' => '',
        'donem' => '',
        'son_odeme' => '',
        'dokum' => '',
        'tutar' => '',
        'remarks' => '',
        'birim_bedel' => '',
        'ilk_okuma' => '',
        'son_okuma' => '',
        'unit' => '',
        'status' => ''
    ];

    protected $rules = [
        // Specify the Livewire property name as the key
        'okumaDegeri' => ['required', 'numeric', 'min:0'],
        'okumaTarihi' => ['required'],
    ];


    public function mount ()
    {
        if (!session('bina_id')) {
            return redirect()->route('binalar');
        }

        $this->action = request()->route('action');

        if ( empty(session()->get(key: 'bina_id'))) {
            redirect('/bina-list');
        }

        $this->current_bina_id = session()->get(key: 'bina_id');
        $this->current_bina_name = session()->get(key: 'selected_bina');

        $this->getSayacliTurler();

        $this->getSakinler();
    }



    public function getSayacliTurler()
    {
        $this->sayacliOkumaTurleri = Bedel::where(
            'bina_id', '=', $this->current_bina_id)->where(
            'tur','=','SAYAC'
        )->get()->toArray();

        $this->activeSayacTab = $this->sayacliOkumaTurleri['0']['id'];

        return true;
    }




    public function render()
    {
        $this->getOkumalar();
        return view('livewire.sayac-okuma');
    }


    public function getSakinler()
    {
        $bina_id = $this->current_bina_id;

        $sakinler = User::where('bina_id', '=', $bina_id)
            ->where('is_sakin', '=', true)
            ->where('is_active', '=', 1)
            ->orderBy('door_no', 'ASC')
            ->get()->toArray();

        $this->sakinler = $sakinler;
    }



    public function getOkumalar()
    {
        foreach ($this->sayacliOkumaTurleri as $key => $sayacTuru) {

            foreach ($this->sakinler as $sakin) {

                $this->okumalar[$sayacTuru['id']][$sakin['id']] = Okuma::where('sakin_id','=',$sakin['id'])
                    ->where('bedel_id','=',$sayacTuru['id'])
                    ->orderBy('created_at','DESC')
                    ->get()
                    ->toArray();

            }
        }
    }




    public function toggleModal($idModal)
    {
        if ( $idModal == 'okumaModal' ) {
            $this->okumaModal = !$this->okumaModal;
        }

        if ( $idModal == 'bedelModal' ) {
            $this->bedelModal = !$this->bedelModal;
        }

    }



    public function yeniOkuma($idSayacTur,$idSakin)
    {
        $this->bedel_id = $idSayacTur;
        $this->currentSakin = User::find($idSakin);
        $this->toggleModal('okumaModal');
    }



    public function okumaKayit($idOkuma = false)
    {
        if ( !$this->currentSakin ) {
            dd('$this->currentSakin yok');
            return;
        }

        $okumaDegerleri['user_id'] = auth()->id();
        $okumaDegerleri['bina_id'] = $this->current_bina_id;
        $okumaDegerleri['bedel_id'] = $this->bedel_id;
        $okumaDegerleri['sakin_id'] = $this->currentSakin->id;
        $okumaDegerleri['okuma_degeri'] = $this->okumaDegeri;
        $okumaDegerleri['okuma_tarihi'] = $this->okumaTarihi;
        $okumaDegerleri['note'] = $this->okumaNotu;
        $okumaDegerleri['status'] = 'OKUNDU';

        if ($idOkuma) {

            //dd($okumaDegerleri);

            // EDIT
            Okuma::find($idOkuma)->update($okumaDegerleri);

        } else {

            // NEW RECORD: CREATE
            if ( !Okuma::create($okumaDegerleri)) {

                dd('Error', $okumaDegerleri);
                return false;
            }
        }

        $this->toggleModal(idModal: 'okumaModal');


        // Yeniden okumaları yükle
        $this->getOkumalar();
        return true;

    }   






    public function okumaDuzenle($idOkuma)
    {

        $this->idOkuma = $idOkuma;

        $okuma = Okuma::find($idOkuma);

        $this->currentSakin = User::find($okuma->sakin_id);

        $this->bedel_id = $okuma->bedel_id;

        $this->okumaTarihi = $okuma->okuma_tarihi;
        $this->okumaDegeri = $okuma->okuma_degeri;
        $this->okumaNotu = $okuma->note;


        $this->toggleModal('okumaModal');

        //dd(vars: $this->okumaTarihi);


    }       



    public function bedelGor($bedel_id,$idOkuma,$idSakin)
    {

        $okumaObject = Bedel::find($bedel_id);

        $this->idOkuma = $idOkuma;

        $this->currentSakin = User::find($idSakin);
        $this->toggleModal('bedelModal');

        $lastTwo = Okuma::where('sakin_id', '=', $idSakin)
            ->where('id', '<=', $idOkuma)
            ->orderBy('okuma_tarihi', 'DESC')
            ->limit(2)
            ->get();

            //dd($lastTwo);

        if ( count($lastTwo) == 1) {

            $ilkOkuma = 0;
            $sonOkuma = $lastTwo[0]->okuma_degeri;

            $ilkOkumaTarihi = '';
            $sonOkumaTarihi = $lastTwo[0]->okuma_tarihi;

        } else {

            $ilkOkuma = $lastTwo[0]->okuma_degeri;
            $sonOkuma = $lastTwo[1]->okuma_degeri;

            $ilkOkumaTarihi = $lastTwo[0]->okuma_tarihi;
            $sonOkumaTarihi = $lastTwo[1]->okuma_tarihi;
        }

        //dd(vars: [$ilkOkuma,$sonOkuma]);


        $toplamTuketim = $ilkOkuma - $sonOkuma;


        $aciklama = $okumaObject->title;

        $tutar = $toplamTuketim * $okumaObject->bedel;

        $dokum = json_encode([
            'son_okuma' => $sonOkuma,
            'ilk_okuma' => $ilkOkuma,
            'birim_bedel' => $okumaObject['bedel'],
            'unit' => $okumaObject['unit']
        ]);




        // Yeni ALACAK kaydı ekle
        $this->okumaBedeli['ilk_okuma'] = $ilkOkuma;
        $this->okumaBedeli['son_okuma'] = $sonOkuma;
        $this->okumaBedeli['ilk_okuma_tarihi'] = $ilkOkumaTarihi;
        $this->okumaBedeli['son_okuma_tarihi'] = $sonOkumaTarihi;

        $this->okumaBedeli['birim_bedel'] = $okumaObject['bedel'];
        $this->okumaBedeli['unit'] = $okumaObject['unit'];

        $this->okumaBedeli['user_id'] = auth()->id();
        $this->okumaBedeli['bina_id'] = $this->current_bina_id;
        $this->okumaBedeli['sakin_id'] = $this->currentSakin->id;
        $this->okumaBedeli['tur'] = 'alacak';
        $this->okumaBedeli['aciklama'] = $aciklama;
        $this->okumaBedeli['donem'] = date('M Y', time());
        $this->okumaBedeli['son_odeme'] = '';
        $this->okumaBedeli['dokum'] = $dokum;
        $this->okumaBedeli['tutar'] = abs($tutar);
        $this->okumaBedeli['status'] = 'KAYIT';

        //dd($okumaBedeli);

                // $this->toggleModal(idModal: 'bedelGorModal');


        // $kayit = Kayit::create($okumaBedeli);

        // $okumaGuncelle = [
        //     'kayit_id' => $kayit->id,
        // ];

        // // Güncelle
        // $okuma = Okuma::find($idOkuma);
        // $okuma = $okuma->update($okumaGuncelle);


        // // Yeniden okumaları yükle
        // $this->getOkumalar();

        // return true;
    }


    public function bedelEkle($idOkuma)
    {
        $this->toggleModal('bedelModal');


        unset($this->okumaBedeli['ilk_okuma']);
        unset($this->okumaBedeli['son_okuma']);
        unset($this->okumaBedeli['ilk_okuma_tarihi']);
        unset($this->okumaBedeli['son_okuma_tarihi']);

        unset($this->okumaBedeli['birim_bedel']);
        unset($this->okumaBedeli['unit']);



        $kayit = Kayit::create($this->okumaBedeli);

        $okumaGuncelle = [
            'kayit_id' => $kayit->id,
            'status' =>'FATURALANDI'
        ];

        // Güncelle
        $okuma = Okuma::find($idOkuma);
        $okuma = $okuma->update($okumaGuncelle);

        $this->okumaBedeli = false;

        // Yeniden okumaları yükle
        $this->getOkumalar();

        return true;
    }

    public function setActiveSayacTab($idTab) {

        $this->activeSayacTab  = $idTab;

    }




}
