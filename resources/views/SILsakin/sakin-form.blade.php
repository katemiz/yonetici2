<x-app-layout>
    <script src="{{ asset('/js/ckeditor5/ckeditor.js') }}"></script>

    <div class="section container">

        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">{{ $sakin ? 'Bilgi Güncelle' :'Bina Sakini Ekle' }}</h1>
        <h2 class="subtitle">{{ $bina->name }} Sakini</h2>

        <div class="column">
            <a href="/bina-view/{{ $bina->id }}">
                <span class="icon-text">
                    <span class="icon">
                        <x-icon icon="arrow-back" fill="{{config('constants.icons.color.active')}}"/>
                    </span>
                    <span>Geri<span>
                </span>
            </a>
        </div>

        <form action="{{ $sakin ? '/sakin-update/'.$bina->id.'/'.$sakin->id : '/sakin-add/'.$bina->id }}" method="{{ $bina ? 'POST' : 'POST' }}" enctype="multipart/form-data">
        @csrf

            <div class="box">

                <div class="columns">

                    <div class="column field is-half">
                        <label class="label" for="name">Ad</label>
                        <div class="control" id="name">
                            <input class="input" name="isim" type="text" placeholder="ad" value="{{ $sakin ? $sakin->name :''}}">
                        </div>
                    </div>

                    <div class="column field">
                        <label class="label" for="surname">Soyad</label>
                        <div class="control" id="surname">
                            <input class="input" name="soyisim" type="text" placeholder="soyad" value="{{ $sakin ? $sakin->lastname :''}}">
                        </div>
                    </div>
                </div>

                <div class="columns">

                    <div class="column field is-half">

                        <label class="label" for="door">Kapı Numarası</label>
                        <div class="control" id="door">
                            <input class="input" name="door_no" type="number" placeholder="kapı numarası" value="{{ $sakin ? $sakin->door_no :''}}">
                        </div>
                    </div>

                    <div class="column field">
                        <label class="label" for="evsahibi">Ev Sahibi/Kiracı</label>

                        <div class="control" id="evsahibi">
                            <label class="radio">
                            <input type="radio" name="sahiplik" value="1" {{$sakin && $sakin->is_evsahibi == '1' ? 'checked': ''}}>
                            Ev Sahibi
                            </label>
                            <br>
                            <label class="radio">
                            <input type="radio" name="sahiplik" value="0" {{$sakin && $sakin->is_evsahibi == '0' ? 'checked': ''}}>
                            Kiracı
                            </label>
                        </div>
                    </div>

                </div>

                <div class="columns">

                    <div class="column field is-half">

                        <label class="label" for="payratio">Genel Ödeme Oranı (%)</label>
                        <div class="control" id="payratio">
                            <input class="input" name="payratio" type="number" step=0.0001 min="50" max="100" placeholder="100"  value="{{$sakin ? $sakin->payratio: ''}}">
                        </div>
                    </div>

                    <div class="column notification is-warning is-light">
                        Bu değer diğer sakinlere oranla aidat gibi genel ödemelere katılma oranını gösterir. m2 veya kişi sayısına bağlı olarak değişebilir.
                        <p class="subtitle">[%50-%100 arasında olmalıdır]</p>
                    </div>

                </div>

                <div class="columns">

                    <div class="column field is-half">

                        <label class="label" for="phone">Telefon Numarası</label>
                        <div class="control" id="phone">
                            <input class="input" name="telno" type="tel" placeholder="telefon numarası" value="{{$sakin ? $sakin->phone: ''}}" >
                        </div>
                    </div>

                    <div class="column field">
                        <label class="label" for="giris">Giriş Tarihi</label>
                        <div class="control" id="giris">
                            <input type="date" name="giristarihi" value="{{$sakin ? $sakin->giris_tarihi: ''}}">
                        </div>
                    </div>

                </div>

                <div class="field">
                    <input type="hidden" name="editor_data" id="ckeditor" value="{{$sakin ? $sakin->remarks : ''}}">
                    <label class="label">Notes/Remarks</label>
                    <div class="column" id="editor">{!!$sakin ? $sakin->remarks: ''!!}</div>
                </div>



                <div class="column field">
                    <label class="label" for="status">Durum</label>

                    <div class="control" id="status">
                        <label class="radio">
                        <input type="radio" name="status" value="1" {{$sakin && $sakin->is_active == '1' ? 'checked': ''}}>
                        {{ $durum['1'] }}
                        </label>
                        <br>
                        <label class="radio">
                        <input type="radio" name="status" value="0" {{$sakin && $sakin->is_active == '0' ? 'checked': ''}}>
                        {{ $durum['0'] }}
                        </label>
                    </div>
                </div>









                <div class="buttons is-right">
                    <button class="button is-link">{{ $sakin ? 'Güncelle' : 'Kaydet'}}</button>
                </div>

            </div>

        </form>
    </div>

    <script>
        ClassicEditor
        .create( document.querySelector('#editor') )
        .then( editor => {
            // console.log(editor);
            let icerik = document.getElementById('ckeditor').value

            if (icerik.length>0) {
                editor.setData(icerik)
            }

            editor.model.document.on('change:data', ( evt, data ) => {
                document.getElementById("ckeditor").value = editor.getData()
            });
        })
        .catch( error => {
            console.error(error);
        });
    </script>

</x-app-layout>
