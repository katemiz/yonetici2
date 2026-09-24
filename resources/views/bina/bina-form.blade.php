<x-app-layout>

    <div class="section container">

        <header class="my-6">
            <h1 class="title has-text-weight-light is-size-1">{{ $bina ? 'Bina Güncelle' : 'Yeni Bina Ekle' }}</h1>
        </header>

        <div class="column box mt-6">

            <form action="{{ $bina ? '/bina-update/'.$bina->id : '/bina-add' }}" method="POST" enctype="multipart/form-data">
            @csrf

                <div class="field">
                    <label class="label">Kullanılacak Para Birimi</label>
                    <div class="field-body">
                        <div class="field">
                        <div class="control">
                            <div class="select" >
                                <select name="parabirimi">
                                    <option value="belirsiz">Para Birimi Seçiniz</option>
                                    @foreach ($paralar as $key => $pbirimi)
                                    <option value="{{$key}}" {{ $bina && $bina->pbirimi && $bina->pbirimi == $key ? 'selected':''}}>{{$pbirimi}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Bina İsmi</label>
                    <div class="control">
                    <input class="input" type="text" name="binaname"
                        placeholder="Pembeköşk Apartmanı"
                        value="{{$bina ? $bina->name : ''}}">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Adres</label>
                    <div class="control">
                    <input class="input" type="text" name="binaaddress"
                        placeholder="Yalıkavak Sok"
                        value="{{$bina ? $bina->address : ''}}">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Şehir</label>
                    <div class="control">
                        <div class="select" >
                            <select name="binacity">
                            <option value="6">Ankara</option>
                            <option value="34">İstanbul</option>
                            </select>
                        </div>

                        <div class="field">
                            <label class="label" for="resident_access_code">Bina sakini giriş kodu</label>
                            <div class="control">
                                <input class="input" id="resident_access_code" type="password"
                                    name="resident_access_code" placeholder="Sakinlerin kullanacağı ortak kod">
                            </div>
                            <p class="help">Sakinler telefon numarası ve bu ortak kod ile yalnızca görüntüleme alanına giriş yapar.</p>
                        </div>
                    </div>
                </div>

                <div class="column has-text-right">
                    <button type="submit" class="button is-link is-light">{{$bina ? 'Güncelle' : 'Kaydet'}}</button>
                </div>

            </form>

        </div>
    </div>

</x-app-layout>
