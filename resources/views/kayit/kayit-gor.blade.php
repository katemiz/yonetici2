<x-app-layout>


    <div class="section container">


        <div class="fixed-grid">
            <div class="grid  mb-6">
                <div class="cell">

                    <h1 class="title has-text-weight-light is-size-1">Kayıt Bilgileri</h1>
                    <h2 class="subtitle has-text-weight-light">Kayıda Ait Olan Tüm Veriler</h2>

                </div>
                <div class="cell has-text-right">

                    <a href="/makbuzpdf/{{ $kayit['id'] }}" class="icon mr-6" aria-label="PDF indir">
                        <i class="fa-regular fa-file-pdf"></i>
                    </a>


                </div>

            </div>
        </div>



        <table class="table is-fullwidth">

            <tr>
                <th>Kayıt No</th>
                <td>{{ $kayit['id'] }}</td>
            </tr>

            <tr>
                <th>Yerleşim Bilgileri</th>
                <td>
                    {{ $bina['name'] }}<br>
                    {{ $bina['address'] }}<br>
                </td>
            </tr>

            @if ($kayit['tur'] != 'gider')

                <tr>
                    <th>Yerleşen Bilgileri</th>
                    <td>
                        {{ $sakinler[$kayit['sakin_id']]['name'] }} {{ $sakinler[$kayit['sakin_id']]['lastname'] }}<br>[
                        Kapı No {{ $sakinler[$kayit['sakin_id']]['door_no'] }} ]
                    </td>
                </tr>

            @endif

            <tr>
                <th>Açıklama</th>
                <td>{{ $kayit['aciklama'] }}</td>
            </tr>

            <tr>
                <th>Ait Olduğu Dönem</th>
                <td>{{ $kayit['donem'] }}</td>
            </tr>

            <tr>
                <th>Tutar</th>
                <td>{{ $kayit['tutar'] }} TL</td>
            </tr>

            <tr>
                <th>Durum</th>
                <td>{{ $kayit['tur'] }}</td>
            </tr>


            @if ($kayit['dokum'])


                {{-- @foreach ($kayit['dokum'] as $key => $value )

                @endforeach --}}


                <tr>
                    <th>Döküm</th>
                    <td>
                        {{-- {{ print_r(value: json_decode($kayit['dokum'], true)) }} --}}

                        @if (isset(json_decode($kayit['dokum'], true)['son_okuma']))

                            <p>Son Okuma :
                                {{ json_decode($kayit['dokum'], true)['son_okuma'] }}
                                {{ json_decode($kayit['dokum'], true)['unit'] }}
                            </p>

                            <p>İlk Okuma :
                                {{ json_decode($kayit['dokum'], true)['ilk_okuma'] }}
                                {{ json_decode($kayit['dokum'], true)['unit'] }}
                            </p>



                            <p>Birim Bedel :
                                {{ json_decode($kayit['dokum'], true)['birim_bedel'] }} TL/
                                {{ json_decode($kayit['dokum'], true)['unit'] }}
                            </p>
                        @endif




                        @if (isset(json_decode($kayit['dokum'], true)['Apartman Aidat']))

                            <p>Apartman Aidat :
                                {{ json_decode($kayit['dokum'], true)['Apartman Aidat'] }}
                            </p>

                        @endif








                    </td>
                </tr>

            @endif

            <tr>
                <th>Diğer Bilgiler</th>
                <td>{!! $kayit['remarks'] !!}</td>
            </tr>

            <tr>
                <th>Dosyalar</th>

                <td class="has-text-left">
                    @foreach ($kayit->dosyalar as $dosya)
                        <a href="/kayit-dosya-gor/{{$dosya->id}}" class="icon-text">
                            <x-icon icon="file" fill="{{config('constants.icons.color.active')}}" />
                            {{ $dosya['filename'] }}
                        </a>


                    @endforeach
                </td>
            </tr>

        </table>



    </div>
</x-app-layout>