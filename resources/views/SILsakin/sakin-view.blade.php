<x-app-layout>

    <div class="section container">

        <h1 class="title has-text-weight-light">Bina Sakini</h1>
        <h2 class="subtitle">Bilgiler</h2>

        {{-- NOTIFICATION --}}
        @if ($notification)
            <div class="notification {{$notification["type"]}} is-light">{!! $notification["message"] !!}</div>
        @endif


        <div class="column">

            <div class="column">
                <a href="/sakin-list/{{ $bina->id }}">
                    <span class="icon-text">
                        <span class="icon">
                            <x-icon icon="arrow-back" fill="{{config('constants.icons.color.active')}}"/>
                        </span>
                        <span>Geri<span>
                    </span>
                </a>
            </div>

            <div class="card mb-4">

                <div class="card-content">
                    <div class="media">
                        <div class="media-content">
                            <p class="title is-4">{{$sakin->name}} {{$sakin->lastname}}</p>

                            <table class="table is-fullwidth">

                                <tr>
                                    <td class="has-text-grey has-text-right">Kapı Numarası</td>
                                    <td>{{ $sakin->door_no }}</td>
                                </tr>

                                <tr>
                                    <td class="has-text-grey has-text-right">Ev Sahipliği Durumu</td>
                                    <td>{{ $sakin->is_evsahibi ? "Ev Sahibi": "Kiracı"}}</td>
                                </tr>

                                <tr>
                                    <td class="has-text-grey has-text-right">Telefon</td>
                                    <td>{{ $sakin->phone }}</td>
                                </tr>

                                <tr>
                                    <td class="has-text-grey has-text-right">Giriş Tarihi</td>
                                    <td>{{ $sakin->giris_tarihi }}</td>
                                </tr>

                                <tr>
                                    <td class="has-text-grey has-text-right">Ödeme Oranı</td>
                                    <td>% {{ $sakin->payratio }}</td>
                                </tr>

                                <tr>
                                    <td class="has-text-grey has-text-right">Notlar/Diğer Bilgiler</td>
                                    <td>{!! $sakin->remarks !!}</td>
                                </tr>

                                <tr>
                                    <td class="has-text-grey has-text-right">Durum</td>
                                    <td>{{ $sakin->is_active ? $durum['1'] : $durum['0']}}</td>
                                </tr>



                            </table>


                        </div>
                    </div>

                </div>

                <footer class="card-footer">

                    <a href="/sakin-form/{{$bina->id}}/{{$sakin->id}}" class="card-footer-item">
                        <x-icon icon="edit" fill="{{config('constants.icons.color.active')}}"/>&nbsp;Değiştir
                    </a>

                    <a href="/sakin-sil/{{$bina->id}}/{{$sakin->id}}" class="card-footer-item">
                        <x-icon icon="delete" fill="{{config('constants.icons.color.danger')}}"/>&nbsp;Sil
                    </a>

                </footer>

            </div>

            <x-card-date :item="$sakin" />

        </div>

    </div>

</x-app-layout>
