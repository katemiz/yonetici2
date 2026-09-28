<x-app-layout>
    <script src="{{ asset('/js/ckeditor5/ckeditor.js') }}"></script>

    <div class="section container">

        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">{{ $bedel ? 'Bedel Tanımı Güncelle' :'Bedel Tanımı' }}</h1>
        <h2 class="subtitle">{{ $bina->name }} için bedel tanımlaması</h2>

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

        <form action="{{ $bedel ? '/bedel-upd/'.$bina->id.'/'.$bedel->id : '/bedel-add/'.$bina->id }}" method="POST" enctype="multipart/form-data">
        @csrf

            <div class="box">

                <div class="field">
                    <label class="label" for="name">Tanım</label>
                    <div class="control" id="name">
                        <input class="input" name="title" type="text" placeholder="Sıcak Su Bedeli veya Hizmetli Ücreti" value="{{ $bedel ? $bedel->title :''}}">
                    </div>
                </div>







                <div class="columns">



                    <div class="column field">
                        <label class="label" >Bedel Türü</label>


                        <div class="control select">
                            <select name="tur">
                                <option value="belirsiz">Tür Seçiniz</option>

                                @foreach ($tur_secenek as $key => $secenek)
                                    <option value="{{$key}}" {{ $bedel && $bedel->tur == $key ? 'selected':''}}>{{$secenek}}</option>
                                @endforeach

                            </select>
                          </div>
                    </div>

                    <div class="column field">

                        <label class="label" for="ucret">Birim Bedel/Ücret</label>
                        <div class="control" id="ucret">
                            <input class="input" name="bedel" type="number" placeholder="25" value="{{ $bedel ? $bedel->bedel :''}}">
                        </div>
                    </div>


                    <div class="column field">
                        <label class="label" for="evsahibi">Birim</label>

                        <div class="control select">
                            <select name="birim">

                                <option value="belirsiz">Birim Seçiniz</option>

                                @foreach ($units as $key => $unit)
                                    <option value="{{$key}}" {{ $bedel && $bedel->unit == $key ? 'selected':''}}>{!!$unit!!}</option>
                                @endforeach

                            </select>
                          </div>
                    </div>



                </div>










                <div class="buttons is-right">
                    <button class="button is-link">{{ $bedel ? 'Güncelle' : 'Kaydet'}}</button>
                </div>

            </div>

        </form>
    </div>

</x-app-layout>
