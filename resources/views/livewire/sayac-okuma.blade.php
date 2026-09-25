<div class="section container">

    <header class="my-6">
        <h1 class="title has-text-weight-light is-size-1">Sayaç Okumaları</h1>
        <h2 class="subtitle has-text-weight-light">Sayaç Okumalarına Tabi Olan Veriler</h2>
    </header>


    {{-- SAYAC TURLERİ : TABS --}}
    <div class="tabs is-boxed">
        <ul>

            @foreach ($sayacliOkumaTurleri as $sayacTur)

                <li class="{{ $activeSayacTab == $sayacTur['id'] ? 'is-active' : '' }}"
                    wire:click="setActiveSayacTab({{ $sayacTur['id'] }})">
                    <a>
                        <span class="icon is-small">
                            <x-icon icon="settings" />
                        </span>
                        <span>{{ $sayacTur['title'] }}</span>
                    </a>
                </li>

            @endforeach

        </ul>
    </div>

    {{-- TAB VALUES --}}
    @foreach ($sayacliOkumaTurleri as $sayacTur)

        <div class="{{ $activeSayacTab == $sayacTur['id'] ? '' : 'is-hidden' }}" id="sayacTab{{ $sayacTur['id'] }}">

            @foreach ($sakinler as $sakin)

                <div class="box mb-4 has-background-light">

                    <div class="fixed-grid has-1-cols-mobile has-2-cols-tablet is-variable is-6 mb-0">
                        <div class="grid ">

                            <div class="cell">
                                <h3 class="title is-4">{{ $sakin['door_no'] }} - {{ $sakin['name'] }} {{ $sakin['lastname'] }}
                                </h3>
                            </div>

                            <div class="cell has-text-right has-text-left-mobile">
                                <button wire:click="yeniOkuma({{ $sayacTur['id'] }},{{ $sakin['id'] }})" class="button is-link">
                                    <span class="icon">
                                        <x-icon icon="plus" />
                                    </span>
                                    <span>Sayaç Okuma Ekle</span>
                                </button>
                            </div>

                        </div>
                    </div>


                    @if (count($okumalar[$sayacTur['id']][$sakin['id']]) > 0)

                        <div class="table-container">
                            <table class="table is-fullwidth is-striped is-hoverable mt-4">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Okuma<br>Tarihi</th>
                                        <th class="has-text-right" wire:model="okumaDegeri">Okuma<br>Değeri</th>
                                        <th>Fatura<br>Durumu</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($okumalar[$sayacTur['id']][$sakin['id']] as $key => $okuma)
                                        <tr>
                                            <td>{{ count($okumalar[$sayacTur['id']][$sakin['id']]) - $key }}. {{ $sayacTur['title'] }}
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($okuma['okuma_tarihi'])->format('d M Y') }}
                                            </td>
                                            <td class="has-text-right is-size-6 has-text-weight-bold">
                                                {{ sprintf("%4d", $okuma['okuma_degeri']) }}
                                            </td>

                                            <td>
                                                @if ($okuma['kayit_id'] > 0)
                                                    <a href="/kayit-gor/{{ $okuma['kayit_id'] }}">{{ $okuma['status'] }}</a>
                                                @else
                                                    {{ $okuma['status'] }}
                                                @endif
                                            </td>

                                            <td>

                                                @if ($okuma['status'] == 'OKUNDU' && $okuma['kayit_id'] < 1)

                                                    <button class="button" wire:click="okumaDuzenle({{ $okuma['id'] }})">
                                                        <span class="icon is-small">
                                                            <x-icon icon="edit" fill="{{config(key: 'constants.icons.color.active')}}" />
                                                        </span>
                                                    </button>

                                                    <button
                                                        wire:click="bedelGor({{ $sayacTur['id'] }},{{ $okuma['id'] }},{{ $okuma['sakin_id'] }})"
                                                        class="button">
                                                        <span class="icon">
                                                            <x-icon icon="reading" />
                                                        </span>
                                                        <span>Bedel</span>
                                                    </button>

                                                @else

                                                    <a href="/kayit-gor/{{ $okuma['kayit_id'] }}" class="button"
                                                        wire:click="okumaDuzenle({{ $okuma['id'] }})">
                                                        <span class="icon is-small">
                                                            <x-icon icon="eye" fill="{{config(key: 'constants.icons.color.active')}}" />
                                                        </span>
                                                    </a>

                                                @endif
                                            </td>
                                        </tr>


                                        @if (strlen($okuma['note']) > 0)
                                            <tr>
                                                <td colspan="5" class="is-size-7 has-text-grey">
                                                    {{ $okuma['note'] }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @endif

                </div>

            @endforeach

        </div>

    @endforeach



    {{-- MODALS --}}
    <div class="modal {{ $okumaModal ? 'is-active' : '' }}" id="okumaModal">

        <div class="modal-background" wire:click="toggleModal('okumaModal')"></div>

        <div class="modal-card">

            <header class="modal-card-head">
                <p class="modal-card-title">
                    {{ $currentSakin ? $currentSakin['name'] . ' ' . $currentSakin['lastname'] : ''}} : Yeni Sayaç Okuma
                </p>
                <button class="delete" aria-label="close" wire:click="toggleModal('okumaModal')"></button>
            </header>

            <section class="modal-card-body">

                <div class="field">
                    <label class="label">Okunan Değer</label>
                    <div class="control">
                        <input class="input" type="number" placeholder="Okuma Değeri" wire:model.live="okumaDegeri">
                    </div>

                    @error('okumaDegeri')
                        <p class="has-text-danger">Sayısal değer giriniz</p>
                    @enderror

                </div>

                <div class="field ">
                    <label class="label" for="okumaTarihi">Okuma Tarihi</label>
                    <div class="control" id="okumaTarihi">
                        <input type="date" name="okumaTarihi" wire:model.live="okumaTarihi" class="input" value="">
                    </div>

                    @error('okumaTarihi')
                        <p class="has-text-danger">Tarih giriniz!</p>
                    @enderror
                </div>


                <div class="field ">
                    <label class="label" for="okumaNotu">Ek Bilgi (Varsa)</label>
                    <div class="control" id="okumaNotu">
                        <textarea class="textarea" placeholder="Ek bilgi var ise ekleyiniz..." name="okumaNotu"
                            wire:model.live="okumaNotu" rows="4"></textarea>
                    </div>
                </div>



            </section>

            <footer class="modal-card-foot">
                <div class="buttons">
                    <button class="button is-success" wire:click="okumaKayit({{ $idOkuma }})">Okuma Kaydet</button>
                    <button class="button" wire:click="toggleModal('okumaModal')">İptal</button>
                </div>
            </footer>
        </div>
    </div>





    <div class="modal {{ $bedelModal ? 'is-active' : '' }}" id="bedelModal">

        <div class="modal-background" wire:click="toggleModal('bedelModal')"></div>

        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">
                    {{ $currentSakin ? $currentSakin['name'] . ' ' . $currentSakin['lastname'] : ''}} : Okuma Bedeli
                    Hazırla
                </p>
                <button class="delete" aria-label="close" wire:click="toggleModal('bedelModal')"></button>
            </header>

            <section class="modal-card-body">


                {{-- {{ print_r(value: $bedel) }} --}}


                <div class="card">
                    <div class="card-content">
                        <div class="media">
                            <div class="media-left">
                                <figure class="image is-48x48">
                                    <x-icon icon="reading" />
                                </figure>
                            </div>
                            <div class="media-content">
                                <p class="title is-4">{{ $okumaBedeli ? $okumaBedeli['aciklama'] : '' }}</p>
                                <p class="subtitle is-6">
                                    {{ $okumaBedeli ? $okumaBedeli['birim_bedel'] : ''}}
                                    {{ $okumaBedeli ? $okumaBedeli['unit'] : ''}} /
                                    TL
                                </p>
                            </div>
                        </div>

                        <div class="content">

                            <table class="table is-fullwidth">
                                <tr>
                                    <th>Son Okuma - İlk Okuma</th>
                                    <td> {{ $okumaBedeli ? $okumaBedeli['son_okuma'] : ''}} -
                                        {{ $okumaBedeli ? $okumaBedeli['ilk_okuma'] : ''}}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Okuma Tarihleri</th>
                                    <td> {{ $okumaBedeli ? $okumaBedeli['son_okuma_tarihi'] : ''}} -
                                        {{ $okumaBedeli ? $okumaBedeli['ilk_okuma_tarihi'] : ''}}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Tutar</th>
                                    <td>{{ $okumaBedeli ? $okumaBedeli['tutar'] : ''}} TL</td>
                                </tr>
                            </table>

                        </div>
                    </div>
                </div>

            </section>

            <footer class="modal-card-foot">
                <div class="buttons">
                    <button class="button is-success" wire:click="bedelEkle({{ $idOkuma }})">Bedel Kaydet</button>
                    <button class="button" wire:click="toggleModal('bedelModal')">İptal</button>
                </div>
            </footer>
        </div>
    </div>




</div>