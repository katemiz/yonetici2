<div class="section container">

    <style>
        .td-tutar {
            white-space: nowrap;
            width: 150px;
            vertical-align: top;
        }
    </style>

    <script>

        let cicon = `<x-icon icon="cancel" fill="{{config('constants.icons.color.danger')}}"/>`

        function searchFunction() {

            if (document.getElementById('queryInput').value.length > 1) {
                @this.call('ara', document.getElementById('queryInput').value)
            }

            return true
        }

        function resetFilter() {
            document.getElementById('queryInput').value = ''
            @this.call('resetFilter')
        }

        function alindiKaydi(kayitId) {

            Swal.fire({
                title: 'Bu alacak gelir kaydına dönüştürülecektir.',
                text: 'Onaylıyor musunuz?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal',

            }).then((result) => {
                if (result.isConfirmed) {
                    window.livewire.emit('alacakToGelir', kayitId)
                } else {
                    return false
                }
            })
        }

        function odendiKaydi(kayitId) {

            Swal.fire({
                title: 'Ödendi Kaydı?',
                text: 'Bu fatura/borç/verecek gider kaydına dönüştürülecektir. Onaylıyor musunuz?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal',

            }).then((result) => {
                if (result.isConfirmed) {
                    window.livewire.emit('verecekToGider', kayitId)
                } else {
                    return false
                }
            })
        }

        function getNames() {

            var newFiles = document.getElementById('fupload')

            if (Object.entries(newFiles.files).length < 1) {
                document.getElementById('noFile').classList.remove('is-hidden')
                return true
            }

            document.getElementById('noFile').classList.add('is-hidden')

            let satir = ''
            dosyalar = []

            for (const [key, dosya] of Object.entries(newFiles.files)) {

                satir = satir + `
                <tr id="K${key}">
                    <td>${dosya.name}</td>
                    <td>${dosya.size}</td>
                    <td>${dosya.type}</td>
                    <td><a onclick="cancelFile('${key}','${dosya.name}')">${cicon}</a></td>
                </tr>`

                dosyalar.push({ key: dosya })
            }

            let thead = document.createElement('thead')

            thead.innerHTML = `
            <tr>
                <th>Dosya Adı</th>
                <th>Boyut</th>
                <th>Dosya Türü</th>
                <th>&nbsp;</th>
            </tr>`

            document.getElementById('filesList').prepend(thead)
            document.getElementById('filesList').innerHTML = satir
        }

        function addFile(kayitId, tur) {
            document.getElementById('add_file').classList.add('is-active')
            document.getElementById('form_add').action = '/kayit-dosya-add/' + kayitId + '/' + tur
        }

        function hideModal() {
            document.getElementById('add_file').classList.remove('is-active')
        }

    </script>

    <header class="my-6">
        <h1 class="title has-text-weight-light is-size-1">{{$html->h1}}</h1>
        <h2 class="subtitle has-text-weight-light">{{$html->h2}}</h2>
    </header>

    {{-- NOTIFICATION --}}
    @if ($notification)
        <div class="notification {{$notification["type"]}} is-light">{!! $notification["message"] !!}</div>
    @endif

    <x-table-filter add="{{$html->addcommand}}" addlink="{{$html->addlink}}"
        showsearch="{{$kayitlar->total() > 0 ? true : false}}" />

    @if ($kayitlar->total() > 0)

        <table class="table is-fullwidth">

            <caption>Toplam <b>{{ $kayitlar->total() }}</b> kayıt vardır</caption>

            <thead>
                <tr>
                    <th>No</th>

                    @if ($table->door_no)
                        <th>Kapı No</th>
                    @endif

                    @if ($table->sakin_id)
                        <th>Borçlu</th>
                    @endif

                    @if ($table->aciklama)
                        <th>
                            <span class="icon-text" wire:click="sortBy('title')">
                                <span class="icon {{ $sortDirection === 'asc' ? 'is-hidden' : ''}}">
                                    <x-icon icon="arrow-up" fill="{{config('constants.icons.color.active')}}" />
                                </span>
                                <span class="icon {{ $sortDirection === 'desc' ? 'is-hidden' : ''}}">
                                    <x-icon icon="arrow-down" fill="{{config('constants.icons.color.active')}}" />
                                </span>
                                <span>Açıklama</span>
                            </span>
                        </th>
                    @endif

                    @if ($table->tutar)
                        <th class="has-text-right">Tutar</th>
                    @endif

                    @if ($table->dosya)
                        <th>&nbsp;</th>
                        <th class="has-text-right">Dosya</th>
                    @endif

                    @if ($table->son_odeme)
                        <th>Son Ödeme</th>
                    @endif

                    @if ($table->created_at)
                        <th class="is-2">
                            <span class="icon-text" wire:click="sortBy('created_at')">
                                <span class="icon {{ $sortTimeDirection === 'asc' ? 'is-hidden' : ''}}">
                                    <x-icon icon="arrow-up" fill="{{config('constants.icons.color.active')}}" />
                                </span>
                                <span class="icon {{ $sortTimeDirection === 'desc' ? 'is-hidden' : ''}}">
                                    <x-icon icon="arrow-down" fill="{{config('constants.icons.color.active')}}" />
                                </span>
                                <span>Eklenme Zamanı</span>
                            </span>
                        </th>
                    @endif

                    @if ($action)
                        <th class="has-text-right is-2">İşlemler</th>
                    @endif

                </tr>
            </thead>

            <tbody>

                @foreach ($kayitlar as $kayit)

                    <tr>

                        <td><a href='/kayit-gor/{{ $kayit['id'] }}'>{{ $kayit['id'] }}</a></td>

                        @if ($table->door_no)
                            <td>
                                @foreach ($bina->sakinler as $sakin)
                                    @if ($sakin->id == $kayit->sakin_id)
                                        {{ $sakin->door_no }}
                                    @endif
                                @endforeach
                            </td>
                        @endif

                        @if ($table->sakin_id)
                            <td>
                                @foreach ($bina->sakinler as $sakin)
                                    @if ($sakin->id == $kayit->sakin_id)
                                        {{ $sakin->name }} {{ $sakin->lastname }}
                                    @endif
                                @endforeach
                            </td>
                        @endif

                        @if ($table->aciklama)
                            <td>
                                {!! $kayit->aciklama !!}
                            </td>
                        @endif

                        @if ($table->tutar)
                            <td class="has-text-right td-tutar">
                                {{ number_format($kayit->tutar, 2, ',', ' ') }}
                            </td>
                        @endif

                        @if ($table->dosya)
                            <td>
                                <a onclick="addFile('{{$kayit->id}}','{{$tur}}')" class="icon mr-6">
                                    <x-icon icon="plus" fill="{{config('constants.icons.color.active')}}" />
                                </a>
                            </td>

                            <td class="has-text-right">
                                @foreach ($kayit->dosyalar as $dosya)
                                    <a href="/kayit-dosya-gor/{{$dosya->id}}" class="icon">
                                        <x-icon icon="file" fill="{{config('constants.icons.color.active')}}" />
                                    </a>
                                @endforeach
                            </td>
                        @endif

                        @if ($table->son_odeme)
                            <td>
                                {{ $kayit->son_odeme }}
                            </td>
                        @endif

                        @if ($table->created_at)
                            <td>{{ $kayit->created_at }}</td>
                        @endif

                        @if ($action)
                            <td class="has-text-right">

                                @if (isset($action['makbuz']) && $action['makbuz'])
                                    <a href="/makbuzpdf/{{$kayit->id}}" class="icon">
                                        <x-icon icon="receipt" fill="{{config('constants.icons.color.active')}}" />
                                    </a>
                                @endif

                                @if (isset($action['view']) && $action['view'])
                                    <a href="/bina-view/{{$kayit->id}}" class="icon">
                                        <x-icon icon="eye" fill="{{config('constants.icons.color.active')}}" />
                                    </a>
                                @endif

                                @if (isset($action['edit']) && $action['edit'])
                                    <a href="/sakinler-form/{bina.id}/{item.id}" class="icon">
                                        <x-icon icon="edit" fill="{{config('constants.icons.color.active')}}" />
                                    </a>
                                @endif

                                @if (isset($action['alindi']) && $action['alindi'])
                                    <a onclick="alindiKaydi('{{$kayit->id}}')" class="icon">
                                        <x-icon icon="income" fill="{{config('constants.icons.color.active')}}" />
                                    </a>
                                @endif

                                @if (isset($action['ödendi']) && $action['ödendi'])
                                    <a onclick="odendiKaydi('{{$kayit->id}}')" class="icon">
                                        <x-icon icon="outbox" fill="{{config('constants.icons.color.active')}}" />
                                    </a>
                                @endif

                            </td>
                        @endif

                    </tr>

                @endforeach

            </tbody>

        </table>

        {{ $kayitlar->links()}}

    @else
        <div class="notification is-warning is-light">{{$html->noitem}}</div>
    @endif

    <div class="modal" id="add_file">
        <div class="modal-background" onclick="hideModal()"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Kayıtlara Dosya Ekleme</p>
                <button class="delete" aria-label="close" onclick="hideModal()"></button>
            </header>

            <form id="form_add" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <section class="modal-card-body">
                    <div class="columns">

                        <div class="column is-narrow">
                            <div class="file is-boxed">
                                <label class="file-label">
                                    <input class="file-input" type="file" name="dosyalar[]" id="fupload" multiple
                                        onchange="getNames()" />
                                    <span class="file-cta">
                                        <span class="file-icon">
                                            <x-icon icon="upload" fill="{{config('constants.icons.color.active')}}" />
                                        </span>
                                        <span class="file-label">Dosyalar</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="column">
                            <table class="table is-striped is-fullwidth">

                                <tbody id="filesList">
                                </tbody>

                                <tfoot id="noFile">
                                    <tr>
                                        <td colspan="4" class="has-text-centered">Henüz seçilmiş dosya yok!</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-success">Yükle</button>
                    <button class="button" onclick="hideModal()">Cancel</button>
                </footer>
            </form>
        </div>
    </div>

</div>