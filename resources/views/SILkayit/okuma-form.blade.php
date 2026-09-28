<x-app-layout>
    <div class="section container">

        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">{{ $okuma ? 'Sayaçlı Okuma Kaydı Güncelle' :'Sayaçlı Okuma Kaydı Ekle' }}</h1>
        <h2 class="subtitle">Sayaca Bağlı Okuma Kaydı</h2>

        <form action="{{ $okuma ? '/okuma-update/'.$tur.'/'.$okuma->id : '/okuma-add/'.$tur }}" method="POST" enctype="multipart/form-data">
        @csrf

            <input type="hidden" id="hiddenTur" name="hiddenTur" value="{{$tur}}" />

            <div class="box">

                <div class="field">

                    <label class="label">Okunacak Veri Tipini Seçiniz</label>

                    <div class="columns">
                    <div class="column is-half">
                        <div class="control">
                            <div class="select is-fullwidth" >
                                <select name="reading_type">
                                    @if (count($okumali_bedeller) > 1)
                                        <option value="">Sayaçlı Okuma Tipini Seçiniz</option>
                                    @endif

                                    @foreach ($okumali_bedeller as $sayacli)
                                        <option value="{{$sayacli->id}}">{{ $sayacli->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>



                    @error('reading_type')
                    <div class="column">
                    <p class="has-text-danger">Veri tipini seçiniz!</p>
                    </div>
                    @enderror
                    </div>
                </div>

                <div class="column">

                    <div class="columns">
                        <div class="column is-1 has-text-weight-bold">No</div>
                        <div class="column is-2 has-text-weight-bold">İsim</div>
                        <div class="column is-3 has-text-weight-bold">Okuma Tarihi</div>
                        <div class="column is-2 has-text-weight-bold">Okunan Değer</div>
                        <div class="column has-text-weight-bold">Notlar</div>
                    </div>

                    @foreach ($bina->sakinler as $sakin)
                        <div class="columns">

                            <div class="column is-1">{{$sakin->door_no}}</div>
                            <div class="column is-2">{{$sakin->name}} {{$sakin->lastname}}</div>
                            <div class="column is-3"><input type="date" name="donem_{{$sakin->id}}" value="{{$okuma ? $okuma->son_odeme: date('Y-m-d')}}"></div>
                            <div class="column is-2">
                                <input class="input" name="okuma_{{$sakin->id}}" type="text" {{ old('okuma', $okuma ? $okuma->son_okuma : '') }}>
                                @error('okuma')
                                <div class="alert alert-danger">Gerekli</div>
                                @enderror
                            </div>
                            <div class="column">
                                <input class="input" name="note_{{$sakin->id}}" type="text" placeholder="Not" value="{{ $okuma ? $okuma->note :''}}">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="buttons is-right">
                    <button class="button is-link" type="submit">{{ $okuma ? 'Güncelle' : 'Kaydet'}}</button>
                </div>

            </div>



        </form>

    </div>


</x-app-layout>
