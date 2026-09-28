<x-app-layout>


    {{session('selected_bina')}}

    <div class="section container">

        <h1 class="title has-text-weight-light">Ayarlar</h1>
        <h2 class="subtitle">{{$bina->name}} : Fiyatlandırma Birim Değerleri</h2>

        <a href="/bina-ayar-view/{{$bina->id}}">
            <span class="icon-text">
                <span class="icon">
                    <x-icon icon="arrow-back" fill="{{config('constants.icons.color.active')}}"/>
                </span>
                <span>Geri<span>
            </span>
        </a>

        <form action="{{ $bina->ayarlar ? '/bina-ayar-update/'.$bina->id.'/'.$bina->ayarlar->id : '/bina-ayar-add/'.$bina->id }}" method="{{ $bina ? 'POST' : 'POST' }}" enctype="multipart/form-data">
            @csrf

            <div class="box">

                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Para Birimi</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <div class="control">
                            <div class="select" >
                                <select name="parabirimi">
                                    <option value="TL" {{$bina->ayarlar !== null && $bina->ayarlar->para_birimi == 'TL' ? 'selected' : ''}}>TL</option>
                                    <option value="USD" {{$bina->ayarlar !== null && $bina->ayarlar->para_birimi == 'USD' ? 'selected' : ''}}>USD</option>
                                    <option value="EUR" {{$bina->ayarlar !== null && $bina->ayarlar->para_birimi == 'EUR' ? 'selected' : ''}}>EUR</option>
                                </select>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Yakıt Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="800" name="yakit"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['yakit'] : ''}}">
                        </p>
                        </div>
                    </div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Ortak Soğuk Su Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="100" name="su"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['su'] : 0}}">
                        </p>
                        </div>
                    </div>
                </div>


                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Ortak Sıcak Su Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="100" name="sicaksu"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['sicak_su'] : 0}}">
                        </p>
                        </div>
                    </div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Elektrik Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="100" name="elektrik"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['elektrik'] : 0}}">
                        </p>
                        </div>
                    </div>
                </div>


                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Asansör Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="100" name="asansor"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['asansor'] : 0}}">
                        </p>
                        </div>
                    </div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Hizmetli/Görevli Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="100" name="hizmetli"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['hizmetli'] : 0}}">
                        </p>
                        </div>
                    </div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Vergi Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="100" name="vergi"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['vergi'] : 0}}">
                        </p>
                        </div>
                    </div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Düzenli Bakım Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="100" name="bakim"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['bakim'] : 0}}">
                        </p>
                        </div>
                    </div>
                </div>


                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Düzenli Onarım Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="100" name="onarim"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['onarim'] : 0}}">
                        </p>
                        </div>
                    </div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Aidat Bedeli</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                        <p class="control">
                            <input class="input" type="number" placeholder="100" name="aidat"
                                value="{{ $bina->ayarlar ? $bina->ayarlar['aidat'] : 0}}">
                        </p>
                        </div>
                    </div>
                </div>

                <div class="buttons is-right">
                    <button class="button is-link is-light">Ayarları Güncelle</button>
                </div>

            </div>
        </form>
    </div>

</x-app-layout>
