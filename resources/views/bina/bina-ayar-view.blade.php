
<x-app-layout>

    <div class="section container">

        <h1 class="title has-text-weight-light">Ayarlar</h1>
        <h2 class="subtitle">{{$bina->name}} : Fiyatlandırma Birim Değerleri</h2>

        <a href="/bina-view/{{$bina->id}}">
            <span class="icon-text">
                <span class="icon">
                    <x-icon icon="arrow-back" fill="{{config('constants.icons.color.active')}}"/>
                </span>
                <span>Geri<span>
            </span>
        </a>

        {{-- NOTIFICATION --}}
        @if ($notification)
            <div class="notification {{$notification["type"]}} is-light my-3">{!! $notification["message"] !!}</div>
        @endif


        <div class="card">
        <div class="card-content">

            <table class="table is-fullwidth">

                <tr>
                    <th class="has-text-grey has-text-right">Para Birimi</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}</td>
                    <td>&nbsp;</td>
                </tr>

                <tr>
                    <th class="has-text-grey has-text-right">Yakıt Bedeli</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->yakit : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}</td>
                </tr>

                <tr>
                    <th class="has-text-grey has-text-right">Su (Ortak Soğuk Su)</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->su : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}/m<sup>3</sup></td>
                </tr>

                <tr>
                    <th class="has-text-grey has-text-right">Sıcak Su (Ortak)</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->sicak_su : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}/m<sup>3</sup></td>
                </tr>

                <tr>
                    <th class="has-text-grey has-text-right">Elektrik (Ortak)</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->elektrik : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}/kwH</td>
                </tr>

                <tr>
                    <th class="has-text-grey has-text-right">Asansör</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->asansor : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}</td>
                </tr>

                <tr>
                    <th class="has-text-grey has-text-right">Hizmetli/Görevli</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->hizmetli : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}</td>
                </tr>

                <tr>
                    <th class="has-text-grey has-text-right">Vergi</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->vergi : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}</td>
                </tr>


                <tr>
                    <th class="has-text-grey has-text-right">Düzenli Bakım</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->bakim : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}</td>
                </tr>

                <tr>
                    <th class="has-text-grey has-text-right">Düzenli Onarım</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->onarim : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}</td>
                </tr>

                <tr>
                    <th class="has-text-grey has-text-right">Aidat (Ortak Harcamalar)</th>
                    <td class=" has-text-right">{{ $bina->ayarlar ? $bina->ayarlar->aidat : 'Henüz Belirlenmemiş' }}</td>
                    <td>{{ $bina->ayarlar ? $bina->ayarlar->para_birimi : 'Henüz Belirlenmemiş' }}</td>
                </tr>


            </table>

            <div class="buttons is-right">
                <a href="/bina-ayar-form/{{$bina->id}}" class="button is-link is-light">
                    <span class="icon-text">
                        <span class="icon">
                            <x-icon icon="edit" fill="{{config('constants.icons.color.active')}}"/>
                        </span>
                        <span> Değiştir</span>
                    </span>
                </a>
            </div>

        </div>
        </div>

    </div>

</x-app-layout>
