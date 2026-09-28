<div class="section container">

    <script src="{{ asset('/js/ckeditor5/ckeditor.js') }}"></script>

    <script>
        let cicon = `<x-icon icon="cancel" fill="{{config('constants.icons.color.danger')}}"/>`

        function addKarar() {

            let karar = {
                title:document.getElementById('k_title').value,
                karar:document.getElementById('ckeditor').value,
            }

            console.log(karar)
            window.livewire.emit('addKarar', karar)
        }

        function showCK() {

            ClassicEditor.create(document.querySelector('#editor'))
            .then((editor) => {
                let icerik = document.getElementById('ckeditor').value

                if (icerik.length > 0) {
                    editor.setData(icerik)
                }
                    editor.model.document.on('change:data', (evt, data) => {
                    document.getElementById('ckeditor').value = editor.getData()
                })
            })
            .catch((error) => {
                console.error(error)
            })

            document.getElementById('modal').classList.add('is-active')
        }

        function destroyCK() {
            document.getElementById('modal').classList.remove('is-active')
            document.querySelector('.ck-editor__editable').ckeditorInstance.destroy()
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

                satir = satir +`
                <tr id="K${key}">
                    <td>${dosya.name}</td>
                    <td>${dosya.size}</td>
                    <td>${dosya.type}</td>
                    <td><a onclick="cancelFile('${key}','${dosya.name}')">${cicon}</a></td>
                </tr>`

                dosyalar.push({key:dosya})
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

    </script>

    <header class="my-6">
        <h1 class="title has-text-weight-light is-size-1">Kararlar</h1>
        <h2 class="subtitle has-text-weight-light">Alınan kararların dökümü</h2>
    </header>

    <nav class="level my-6">

        <!-- Left side -->
        <div class="level-left">
            <div class="level-item  has-text-centered">
                <a href="javascript:showCK()" class="button is-link">
                    <span class="icon is-small">
                        <x-icon icon="plus" fill="{{config('constants.icons.color.light')}}"/>
                    </span>
                    <span>Karar Ekle</span>
                </a>
            </div>
        </div>

        @if ($kararlar->total() > 0)
        <!-- Right side -->
        <div class="level-right">

            <div class="level-item">
                <!-- Filter Tree Search Box -->
                <div class="field has-addons is-fullwidth is-pulled-right">

                    <p class="control has-icons-left">
                        <input class="input" type="text" placeholder="ara..." oninput="searchFunction()" id="queryInput">
                        <span class="icon is-small is-left">
                            <x-icon icon="search" fill="{{config('constants.icons.color.disabled')}}"/>
                        </span>
                    </p>
                    <p class="control">
                        <a class="button px-1" onclick="resetFilter()">
                            <x-icon icon="cancel" fill="{{config('constants.icons.color.active')}}"/>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        @endif


    </nav>


    @if ($kararlar->total() > 0)

        <table class="table is-fullwidth">

            <caption>Toplam <b>{{ $kararlar->total() }}</b> karar kaydı vardır</caption>

            <thead>
                <tr>
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
                                <x-icon icon="arrow-up" fill="{{config('constants.icons.color.active')}}"/>
                            </span>
                            <span class="icon {{ $sortDirection === 'desc' ? 'is-hidden' : ''}}">
                                <x-icon icon="arrow-down" fill="{{config('constants.icons.color.active')}}"/>
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
                                <x-icon icon="arrow-up" fill="{{config('constants.icons.color.active')}}"/>
                            </span>
                            <span class="icon {{ $sortTimeDirection === 'desc' ? 'is-hidden' : ''}}">
                                <x-icon icon="arrow-down" fill="{{config('constants.icons.color.active')}}"/>
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
                    @if ($table->door_no)
                    <td>
                        @foreach ($bina->sakinler as $sakin)
                            @if ($sakin->id == $kayit->sakin_id )
                                {{ $sakin->door_no }}
                            @endif
                        @endforeach
                    </td>
                    @endif

                    @if ($table->sakin_id)
                    <td>
                        @foreach ($bina->sakinler as $sakin)
                            @if ($sakin->id == $kayit->sakin_id )
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
                            <x-icon icon="plus" fill="{{config('constants.icons.color.active')}}"/>
                            </a>
                        </td>

                        <td class="has-text-right">
                            @foreach ($kayit->dosyalar as $dosya)
                                <a href="/kayit-dosya-gor/{{$dosya->id}}" class="icon">
                                    <x-icon icon="file" fill="{{config('constants.icons.color.active')}}"/>
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
                        <a href="/makbuz/{{$kayit->id}}" class="icon">
                            <x-icon icon="receipt" fill="{{config('constants.icons.color.active')}}"/>
                        </a>
                        @endif

                        @if (isset($action['view']) && $action['view'])
                        <a href="/bina-view/{{$kayit->id}}" class="icon">
                            <x-icon icon="eye" fill="{{config('constants.icons.color.active')}}"/>
                        </a>
                        @endif

                        @if (isset($action['edit']) && $action['edit'])
                        <a href="/sakinler-form/{bina.id}/{item.id}" class="icon">
                            <x-icon icon="edit" fill="{{config('constants.icons.color.active')}}"/>
                        </a>
                        @endif

                        @if (isset($action['alindi']) && $action['alindi'])
                        <a onclick="alindiKaydi('{{$kayit->id}}')" class="icon">
                            <x-icon icon="income" fill="{{config('constants.icons.color.active')}}"/>
                        </a>
                        @endif

                        @if (isset($action['ödendi']) && $action['ödendi'])
                        <a onclick="odendiKaydi('{{$kayit->id}}')" class="icon">
                            <x-icon icon="outbox" fill="{{config('constants.icons.color.active')}}"/>
                        </a>
                        @endif

                    </td>
                    @endif

                </tr>

                @endforeach

            </tbody>

        </table>

        {{ $kararlar->links()}}

    @else
        <div class="notification is-warning is-light">Ekli bir karar kaydı bulunmamaktadır.</div>
    @endif







    <div class="modal" id="modal">
        <div class="modal-background" onclick="destroyCK()"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Karar</p>
                <button class="delete" aria-label="close" onclick="destroyCK()"></button>
            </header>

            <form wire:submit.prevent="" method="POST" enctype="multipart/form-data">
            @csrf
                <section class="modal-card-body">

                    <div class="field">
                        <label class="label">Karar Konusu</label>
                        <div class="control">
                        <input class="input" type="text" name="title" id="k_title"
                            placeholder="Örneğin Aidat Arttırımı"
                            value="{{$karar ? $karar->title : ''}}">
                        </div>
                    </div>

                    <div class="field" id="ck">
                        <input type="hidden" name="editor_data" id="ckeditor" value="{{$karar ? $karar->metin : ''}}">
                        <label class="label">Karar Metni/Açıklaması</label>
                        <div class="column" id="editor">{!!$karar ? $karar->metin: ''!!}</div>
                    </div>

                    <div class="columns">

                        <div class="column is-narrow">
                            <div class="file is-boxed">
                                <label class="file-label">
                                <input
                                    class="file-input"
                                    type="file"
                                    name="dosyalar[]"
                                    id="fupload"
                                    multiple
                                    onchange="getNames()" />
                                <span class="file-cta">
                                    <span class="file-icon">
                                    <x-icon icon="upload" fill="{{config('constants.icons.color.active')}}"/>
                                    </span>
                                    <span class="file-label">Dosyalar</span>
                                </span>
                                </label>
                            </div>
                        </div>

                        <div class="column">
                            <table class="table is-striped is-fullwidth" >

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
                    <button class="button is-success" onclick="addKarar()">Ekle</button>
                    <a class="button" onclick="destroyCK()">İptal</a>
                </footer>
            </form>
        </div>
    </div>










</div>
