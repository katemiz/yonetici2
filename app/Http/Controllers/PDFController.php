<?php

namespace App\Http\Controllers;

use App\Models\Bina;
use App\Models\Kayit;
use App\Models\Sakin;
use App\Models\User;
use Carbon\Carbon;
use Hamcrest\Type\IsNumeric;
use Illuminate\Http\Request;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PDFController extends Controller
{
    public $bina;
    public $yonetici;
    public $kayit;
    public $borclu;
    public $sakinler;

    public function initialize()
    {

        if (!session('bina_id')) {
            return redirect()->route('binalar');
        }

        $this->bina = Bina::find(session('bina_id'));
        $this->yonetici = User::find($this->bina->user_id);
    }


    public function getData($idKayit)
    {
        $this->kayit = Kayit::find($idKayit);

        $this->borclu['yazi'] = '-';
        $this->borclu['kapino'] = '-';
        $this->borclu['isim'] = '-';

        if (is_numeric($this->kayit->sakin_id) && $this->kayit->sakin_id > 0) {
            $owner = Sakin::find($this->kayit->sakin_id);

            $this->borclu['yazi'] =
                'Yalnız ' .
                $this->numberToText($this->kayit->tutar) .
                ' TL tahsil edilmiştir.';
            $this->borclu['kapino'] = $owner->door_no;
            $this->borclu['isim'] = $owner->name . ' ' . $owner->lastname;
        }
    }

    /**
     * Write code on Method
     *
     * @return response()
     */

    public function dolumakbuz(Request $request)
    {
        $this->initialize();
        $record = $request->route('record');
        $this->getData($record);

        return $this->preparePdfFile($record);
    }


    public function bosmakbuz()
    {
        $this->initialize();

        //$this->kayit = Kayit::find(393);
        $this->kayit = false;


        $this->borclu['yazi'] = '';
        $this->borclu['kapino'] = '';
        $this->borclu['isim'] = '';


        //$this->kayit['tutar'] = '';

        //dd($this->kayit);




        $pdf = new TCPDF();

        $pdf::SetAutoPageBreak(false);


        $pdf = $this->prepareSinglePage($pdf);


        return response()->make($pdf::Output('bosmakbuz.pdf', 'I'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }





    public function aylikaidatlar(Request $request)
    {

        $this->initialize();

        $son_kayitlar = Kayit::where('bina_id', $this->bina->id)
            ->whereNotNull('dokum')
            ->orderBy('id', 'desc')
            ->limit(count($this->bina->sakinler))->get();

        $dizin = [];

        foreach ($son_kayitlar as $k) {
            $dizin[] = $k->id;
        }

        $this->preparePdfFile($dizin);
    }


    public function preparePdfFile($recordId)
    {

        $pdf = new TCPDF();

        $pdf::SetAutoPageBreak(false);

        if (is_array($recordId)) {

            $filename = 'MakbuzAylik.pdf';

            foreach ($recordId as $rid) {
                $this->getData($rid);
                $pdf = $this->prepareSinglePage($pdf);
            }
        } else {

            $filename = 'Makbuz' . $recordId . '.pdf';

            $this->getData($recordId);
            $pdf = $this->prepareSinglePage($pdf);
        }

        return response()->make($pdf::Output($filename, 'I'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function prepareSinglePage($pdf)
    {
        $pdf::AddPage('L', 'A5');

        $style = [
            'width' => 0.25,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => 0,
            'color' => [0, 64, 128],
        ];

        // OUTER RECTANGLE
        $pdf::Rect(10, 10, 190, 128, 'D', ['all' => $style]);
        $pdf::Line(10, 30, 200, 30, ['all' => $style]);

        // ICON
        $pdf::ImageSVG(
            $file = public_path('images/favicon.svg'),
            $x = 13,
            $y = 13,
            $w = '14',
            $h = '14',
            $link = 'https://yonetici.kapkara.one',
            $align = '',
            $palign = '',
            $border = 0,
            $fitonpage = false
        );

        $pdf::SetFillColor(240, 240, 240);

        // BİNA İSİM VE ADRES
        $pdf::SetFont('dejavusans', '', 12);

        $pdf::MultiCell(
            $w = 80,
            $h = 6,
            $txt = $this->bina->name,
            $border = 0,
            $align = 'L',
            $fill = 0,
            1,
            $x = 30,
            $y = 14,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::SetFont('dejavusans', '', 8);

        $pdf::MultiCell(
            $w = 80,
            $h = 6,
            $txt = $this->bina->address,
            $border = 0,
            $align = 'L',
            $fill = 0,
            1,
            $x = 30,
            $y = 20,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        // HEADER
        $pdf::SetXY(106, 14);
        $pdf::SetFont('dejavusans', 'B', 18);

        $pdf::MultiCell(
            $w = 90,
            $h = 20,
            $txt = 'GELİR MAKBUZU',
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 110,
            $y = 10,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 20,
            $valign = 'M'
        );

        // QR CODE
        $style2 = [
            'border' => false,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
        ];

        $pdf::write2DBarcode(
            url()->full(),
            'QRCODE,H',
            95,
            35,
            20,
            20,
            $style2,
            'N'
        );

        // TARİH
        $pdf::SetFont('dejavusans', '', 8);

        $pdf::MultiCell(
            $w = 75,
            $h = 6,
            $txt = Carbon::now(),
            $border = 0,
            $align = 'R',
            $fill = 1,
            1,
            $x = 120,
            $y = 35,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        // KAPI NO, İSİM VE DÖNEM
        $pdf::MultiCell(
            $w = 23,
            $h = 6,
            $txt = 'Kapı No',
            $border = 0,
            $align = 'R',
            $fill = 1,
            1,
            $x = 120,
            $y = 43,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 50,
            $h = 6,
            $txt = $this->borclu['kapino'],
            $border = 0,
            $align = 'R',
            $fill = 1,
            1,
            $x = 145,
            $y = 43,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 23,
            $h = 6,
            $txt = 'Ad-Soyad',
            $border = 0,
            $align = 'R',
            $fill = 1,
            1,
            $x = 120,
            $y = 51,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 50,
            $h = 6,
            $txt = $this->borclu['isim'],
            $border = 0,
            $align = 'R',
            $fill = 1,
            1,
            $x = 145,
            $y = 51,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 23,
            $h = 6,
            $txt = 'Dönem',
            $border = 0,
            $align = 'R',
            $fill = 1,
            1,
            $x = 120,
            $y = 59,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );



        // DÖNEM
        if ($this->kayit) {

            if (Str::contains($this->kayit->donem, '-')) {
                $donem = explode('-', $this->kayit->donem)[2];
            } else {
                $donem = $this->kayit->donem;
            }

        } else {
            $donem = '';
        }







        $pdf::MultiCell(
            $w = 50,
            $h = 6,
            $txt = $donem,
            $border = 0,
            $align = 'L',
            $fill = 0,
            1,
            $x = 145,
            $y = 59,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 50,
            $h = 6,
            $txt = '',
            $border = 0,
            $align = 'R',
            $fill = 1,
            1,
            $x = 145,
            $y = 59,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::SetFont('dejavusans', '', 6);

        $pdf::MultiCell(
            $w = 20,
            $h = 6,
            $txt = $this->kayit ? 'Belge No ' . $this->kayit->id : 'Belge No ',
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 95,
            $y = 56,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        // TUTAR
        $pdf::SetFont('dejavusans', 'B', 20);
        $pdf::MultiCell(
            $w = 100,
            $h = 16,
            $txt = $this->kayit ? number_format($this->kayit->tutar, 2, ',', ' ') . ' TL' : '',
            $border = 'B',
            $align = 'R',
            $fill = 1,
            1,
            $x = 95,
            $y = 72,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        // YAZI İLE AÇIKLAMA
        $pdf::SetFont('dejavusans', '', 10);
        $pdf::SetFillColor(240, 240, 240);

        $pdf::MultiCell(
            $w = 100,
            $h = 20,
            $txt = $this->borclu['yazi'],
            $border = 0,
            $align = 'L',
            $fill = 1,
            1,
            $x = 95,
            $y = 93,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 20,
            $valign = 'T'
        );

        // MAKBUZU VEREN
        $pdf::SetFont('dejavusans', '', 6);

        $pdf::MultiCell(
            $w = 95,
            $h = 6,
            $txt = $this->bina->name . ' Yöneticisi',
            $border = 'T',
            $align = 'C',
            $fill = 1,
            1,
            $x = 10,
            $y = 118,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::SetFont('dejavusans', 'B', 12);

        $pdf::MultiCell(
            $w = 95,
            $h = 14,
            $txt =
            $this->yonetici->name .
            ' ' .
            strtoupper($this->yonetici->lastname),
            $border = 0,
            $align = 'C',
            $fill = 1,
            1,
            $x = 10,
            $y = 124,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 14,
            $valign = 'M'
        );

        $pdf::SetFont('dejavusans', '', 6);

        $pdf::MultiCell(
            $w = 95,
            $h = 6,
            $txt = 'Mühür ve İmza',
            $border = 'T',
            $align = 'C',
            $fill = 1,
            1,
            $x = 105,
            $y = 118,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::Rect(15, 35, 75, 78, 'D', ['all' => $style]);

        $starty = 35;
        $startx = 15;

        $title_w = 55;

        $header = 8;
        $pdf::SetFont('dejavusans', 'B', 10);

        $pdf::MultiCell(
            $w = 75,
            $h = $header,
            $txt = 'ALINDI DÖKÜMÜ',
            $border = 'B',
            $align = 'C',
            $fill = 1,
            1,
            $x = $startx,
            $y = $starty,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = $header,
            $valign = 'M'
        );

        $uz = $starty + $header;

        $pdf::SetFont('dejavusans', '', 6);

        if ($this->kayit) {
            foreach (json_decode($this->kayit->dokum) as $title => $deger) {

                if (is_numeric($deger)) {
                    $deger = number_format($deger, 2, ',', ' ');
                }


                $pdf::MultiCell(
                    $w = $title_w,
                    $h = 6,
                    $txt = $title,
                    $border = 'B',
                    $align = 'L',
                    $fill = 0,
                    1,
                    $x = $startx,
                    $y = $uz,
                    $reseth = true,
                    $strech = 0,
                    $ishtml = false,
                    $autopadding = true,
                    $maxh = 6,
                    $valign = 'M'
                );

                $pdf::MultiCell(
                    $w = 20,
                    $h = 6,
                    $txt = $deger,
                    $border = 'LB',
                    $align = 'R',
                    $fill = 0,
                    1,
                    $x = $startx + $title_w,
                    $y = $uz,
                    $reseth = true,
                    $strech = 0,
                    $ishtml = false,
                    $autopadding = true,
                    $maxh = 6,
                    $valign = 'M'
                );

                $uz = $uz + 6;
            }
        }

        if ($this->kayit) {

            $pdf::MultiCell(
                $w = $title_w,
                $h = 6,
                $txt = $this->kayit->aciklama,
                $border = 'B',
                $align = 'L',
                $fill = 0,
                1,
                $x = $startx,
                $y = $uz,
                $reseth = true,
                $strech = 0,
                $ishtml = false,
                $autopadding = true,
                $maxh = 6,
                $valign = 'M'
            );

            $pdf::MultiCell(
                $w = 20,
                $h = 6,
                $txt = number_format($this->kayit->tutar, 2, ',', ' '),
                $border = 'LB',
                $align = 'R',
                $fill = 0,
                1,
                $x = $startx + $title_w,
                $y = $uz,
                $reseth = true,
                $strech = 0,
                $ishtml = false,
                $autopadding = true,
                $maxh = 6,
                $valign = 'M'
            );
        }

        // DÖKÜM TOPLAM
        $pdf::MultiCell(
            $w = $title_w,
            $h = 6,
            $txt = 'TOPLAM',
            $border = 'T',
            $align = 'L',
            $fill = 1,
            1,
            $x = $startx,
            $y = 107,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 20,
            $h = 6,
            $txt = $this->kayit ? number_format($this->kayit->tutar, 2, ',', ' ') : '',
            $border = 'LT',
            $align = 'R',
            $fill = 1,
            1,
            $x = $startx + $title_w,
            $y = 107,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 95,
            $h = 6,
            $txt = Config::get('constants.app.title'),
            $border = 0,
            $align = 'L',
            $fill = 0,
            1,
            $x = 10,
            $y = 138,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 95,
            $h = 6,
            $txt = url('/'),
            $border = 0,
            $align = 'R',
            $fill = 0,
            1,
            $x = 105,
            $y = 138,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 6,
            $valign = 'M'
        );

        return $pdf;
    }


    public function getBinaData($idBina)
    {
        $this->bina = Bina::find($idBina);
        $this->yonetici = User::find($this->bina->user_id);

        if ($this->bina->user_id !== Auth::id()) {
            abort('403');
        }

        foreach ($this->bina->sakinler as $sakin) {
            $this->sakinler[$sakin->id] = $sakin->name . ' ' . $sakin->lastname;
        }
    }


    public function gelirler()
    {
        return Kayit::where('bina_id', $this->bina->id)
            ->where('tur', '=', 'gelir')
            ->get();
    }


    public function giderler()
    {
        return Kayit::where('bina_id', $this->bina->id)
            ->where('tur', '=', 'gider')
            ->get();
    }


    public function dokum(Request $request)
    {
        if (!session('bina_id')) {
            return redirect()->route('binalar');
        }

        $this->getBinaData(session('bina_id'));

        $filename = 'GelirGiderDokumu' . $this->bina->id . '.pdf';

        $pdf = new TCPDF();

        $pdf::SetAutoPageBreak(false);
        $pdf::AddPage('P', 'A4');

        $pdf::SetFont('dejavusans', 'B', 20);

        $pdf::MultiCell(
            $w = 190,
            $h = 16,
            $txt = Config::get('constants.app.welcome_subtitle'),
            $border = 0,
            $align = 'L',
            $fill = 0,
            1,
            $x = 10,
            $y = 15,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'B'
        );

        $pdf::SetFont('dejavusans', '', 14);

        $pdf::MultiCell(
            $w = 190,
            $h = 16,
            $txt = $this->bina->name . ' Gelir-Gider Durum Dökümü',
            $border = 0,
            $align = 'L',
            $fill = 0,
            1,
            $x = 10,
            $y = 22,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'B'
        );

        // ICON
        $pdf::ImageSVG(
            $file = public_path('images/kapak.svg'),
            $x = 50,
            $y = 60,
            $w = '110',
            $h = '110',
            $link = 'https://yonetici.kapkara.one',
            $align = '',
            $palign = '',
            $border = 0,
            $fitonpage = false
        );

        $pdf::SetFont('dejavusans', 'B', 22);

        $pdf::MultiCell(
            $w = 190,
            $h = 16,
            $txt = $this->bina->name,
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 10,
            $y = 200,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        $pdf::SetFont('dejavusans', '', 18);

        $pdf::MultiCell(
            $w = 190,
            $h = 16,
            $txt = $this->bina->address,
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 10,
            $y = 215,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        $pdf::SetFont('dejavusans', 'B', 17);

        $pdf::MultiCell(
            $w = 190,
            $h = 16,
            $txt = date('d M Y', time()),
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 10,
            $y = 260,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        $pdf::SetFont('dejavusans', '', 12);

        $pdf::MultiCell(
            $w = 190,
            $h = 16,
            $txt = url('/'),
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 10,
            $y = 270,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        $pdf::AddPage('P', 'A4');

        $pdf::SetFillColor(240, 240, 240);

        // GELİR-GİDER OZET

        $ozet = $this->ozet();

        $pdf::SetFont('dejavusans', 'B', 22);

        $pdf::MultiCell(
            $w = 190,
            $h = 16,
            $txt = 'Gelir-Gider Özet',
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 10,
            $y = 30,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        $pdf::SetFont('dejavusans', '', 14);

        $pdf::MultiCell(
            $w = 60,
            $h = 16,
            $txt = 'Gelir',
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 15,
            $y = 50,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        $pdf::SetFont('dejavusans', 'B', 20);

        $pdf::MultiCell(
            $w = 60,
            $h = 16,
            $txt = $ozet->toplam_gelir,
            $border = 'LRT',
            $align = 'C',
            $fill = 1,
            1,
            $x = 15,
            $y = 70,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 60,
            $h = 16,
            $txt = 'Gider',
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 75,
            $y = 50,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        $pdf::MultiCell(
            $w = 60,
            $h = 16,
            $txt = 'Bakiye',
            $border = 0,
            $align = 'C',
            $fill = 0,
            1,
            $x = 135,
            $y = 50,
            $reseth = true,
            $strech = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 16,
            $valign = 'M'
        );

        //dd('here');


        return response()->make($pdf::Output($filename, 'I'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }


    public function ozet()
    {
        // Alacaklar
        $alacak_top_tutar = Kayit::where([
            ['bina_id', '=', $this->bina->id],
            ['tur', '=', 'alacak'],
        ])->sum('tutar');

        // Verecekler - Faturalar
        $verecek_top_tutar = Kayit::where([
            ['bina_id', '=', $this->bina->id],
            ['tur', '=', 'verecek'],
        ])->sum('tutar');

        // Gelirler
        $gelirler_top_tutar = Kayit::where([
            ['bina_id', '=', $this->bina->id],
            ['tur', '=', 'gelir'],
        ])->sum('tutar');

        // Giderler
        $giderler_top_tutar = Kayit::where([
            ['bina_id', '=', $this->bina->id],
            ['tur', '=', 'gider'],
        ])->sum('tutar');

        $nakit = $gelirler_top_tutar - $giderler_top_tutar;

        $ozet['toplam_alacak'] = number_format($alacak_top_tutar, 2, ',', ' ');
        $ozet['toplam_borc'] = number_format($verecek_top_tutar, 2, ',', ' ');
        $ozet['toplam_gelir'] = number_format($gelirler_top_tutar, 2, ',', ' ');
        $ozet['toplam_gider'] = number_format($giderler_top_tutar, 2, ',', ' ');
        $ozet['nakit'] = number_format($nakit, 2, ',', ' ');

        return (object) $ozet;
    }


    public function numberToText($number)
    {
        if ($number < 10) {

            if ($number < 1) {
                return "sıfır";
            }

            return $this->toBirler($number);
        }

        if ($number < 100 && $number > 9) {
            return $this->toOnlar($number);
        }

        if ($number < 1000 && $number > 99) {
            return $this->toYuzler($number);
        }

        if ($number < 10000 && $number > 999) {
            return $this->toBinler($number);
        }

        if ($number < 100000 && $number > 9999) {
            return $this->toOnbinler($number);
        }

        if ($number < 1000000 && $number > 99999) {
            return $this->toYuzbinler($number);
        }

        if ($number < 100000000 && $number > 999999) {
            return $this->toMilyonlar($number);
        }
    }


    public function toBirler($number)
    {

        $birler = [
            0 => '',
            1 => 'bir',
            2 => 'iki',
            3 => 'üç',
            4 => 'dört',
            5 => 'beş',
            6 => 'altı',
            7 => 'yedi',
            8 => 'sekiz',
            9 => 'dokuz',
        ];

        return $birler[$number];
    }


    public function toOnlar($number)
    {

        $onlar = [
            1 => 'on',
            2 => 'yirmi',
            3 => 'otuz',
            4 => 'kırk',
            5 => 'elli',
            6 => 'altmış',
            7 => 'yetmiş',
            8 => 'seksen',
            9 => 'doksan',
        ];

        if ($number < 1) {
            return null;
        }

        $s[] = $onlar[substr($number, 0, 1)];

        if (substr($number, 1, 1) > 0) {
            $s[] = $this->toBirler(substr($number, 1, 1));
        }

        return implode(' ', $s);
    }


    public function toYuzler($number)
    {

        $yuzler = [
            1 => 'yüz',
            2 => 'iki yüz',
            3 => 'üç yüz',
            4 => 'dört yüz',
            5 => 'beş yüz',
            6 => 'altı yüz',
            7 => 'yedi yüz',
            8 => 'sekiz yüz',
            9 => 'dokuz yüz',
        ];

        $number = ltrim($number, "0");

        if ($number > 0 && $number < 10) {
            return $this->toBirler($number);
        }

        if ($number > 10 && $number < 100) {
            return $this->toOnlar($number);
        }

        if ($number > 0) {
            $s[] = $yuzler[substr($number, 0, 1)];
        } else {
            return null;
        }

        if (substr($number, 1, 1) > 0) {
            $s[] = $this->toOnlar(substr($number, 1, 1));
        }

        if (substr($number, 2, 1) > 0) {
            $s[] = $this->toBirler(substr($number, 2, 1));
        }

        return implode(' ', $s);
    }


    public function toBinler($number)
    {

        $binler = [
            1 => 'bin',
            2 => 'iki bin',
            3 => 'üç bin',
            4 => 'dört bin',
            5 => 'beş bin',
            6 => 'altı bin',
            7 => 'yedi bin',
            8 => 'sekiz bin',
            9 => 'dokuz bin',
        ];

        $s[] = $binler[substr($number, 0, 1)];

        if (substr($number, 1) > 0) {
            $s[] = $this->toYuzler(substr($number, 1));
        }

        return implode(' ', $s);
    }


    public function toOnbinler($number)
    {

        $s[] = $this->toOnlar(substr($number, 0, 2));
        $s[] = $this->toYuzler(substr($number, 2));

        return trim(implode(' bin ', $s));
    }


    public function toYuzbinler($number)
    {

        $s[] = $this->toYuzler(substr($number, 0, 3));
        $s[] = $this->toYuzler(substr($number, 3));

        return trim(implode(' bin ', $s));
    }


    public function toMilyonlar($number)
    {

        $s[] = $this->toYuzler(substr($number, 0, -6));
        $s[] = $this->toYuzbinler(substr($number, -6));

        return trim(implode(' milyon ', $s));
    }
}
