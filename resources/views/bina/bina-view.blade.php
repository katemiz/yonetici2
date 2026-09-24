<div class="section container">

    <h1 class="title has-text-weight-light">{{$bina->name}}</h1>
    <h2 class="subtitle">Özellikler</h2>

    <x-icon icon="arrow_back" fill="{{config('constants.icons.color.active')}}" />


    <a href="/bina-list">
        <span class="icon-text">
            <span class="icon">
                <x-icon icon="arrow-back" fill="{{config('constants.icons.color.active')}}" />
            </span>
            <span>Geri<span>
                </span>
    </a>


    {{-- NOTIFICATION --}}
    @if ($notification)
        <div class="notification {{$notification["type"]}} is-light">{!! $notification["message"] !!}</div>
    @endif

    <div class="columns mt-6">

        <div class="column is-3">

            <aside class="menu">

                <p class="menu-label">Menu</p>
                <ul class="menu-list">
                    <li>
                        <a href="/sakin-list/{{ $bina->id }}">
                            <span class="icon-text">
                                <span class="icon">
                                    <x-icon icon="sakin" fill="{{config('constants.icons.color.active')}}" />
                                </span>
                            </span> Bina Sakinleri <span>
                            </span>
                        </a>
                    </li>

                    {{-- <li>
                        <a href="/bina-ayar/{{ $bina->id }}">
                            <span class="icon-text">
                                <span class="icon">
                                    <x-icon icon="settings" fill="{{config('constants.icons.color.active')}}" />
                                </span>
                                <span>Ayarlar </span>
                            </span>
                        </a>
                    </li> --}}

                    <li>
                        <a href="/bedel-list/{{ $bina->id }}">
                            <span class="icon-text">
                                <span class="icon">
                                    <x-icon icon="settings" fill="{{config('constants.icons.color.active')}}" />
                                </span>
                                <span>Hizmet Bedelleri</span>
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="/kalem-list/{{ $bina->id }}">
                            <span class="icon-text">
                                <span class="icon">
                                    <x-icon icon="settings" fill="{{config('constants.icons.color.active')}}" />
                                </span>
                                <span>Harcama Türleri</span>
                            </span>
                        </a>
                    </li>



                    <li>
                        <a href="/sayac-okuma/{{ $bina->id }}">
                            <span class="icon-text">
                                <span class="icon">
                                    <x-icon icon="settings" fill="{{config('constants.icons.color.active')}}" />
                                </span>
                                <span>Sayaç Okumaları</span>
                            </span>
                        </a>
                    </li>



                </ul>

            </aside>

        </div>

        <div class="column">

            <div class="card mb-6">

                <div class="card-content">
                    <div class="media">
                        <div class="media-content">
                            <p class="title is-4">{{ $bina->name}}</p>
                        </div>
                    </div>

                    <div class="content">
                        {{ $bina->address }}
                        <p class="mt-3">Kullanılan Para Birimi : {{ $bina->pbirimi }}</p>
                    </div>
                </div>

                <footer class="card-footer">

                    <a href="/bina-form/{{ $bina->id }}" class="card-footer-item">
                        <x-icon icon="edit" fill="{{config('constants.icons.color.active')}}" />&nbsp;Değiştir
                    </a>

                    <a href="/bina-sil/{{ $bina->id }}" class="card-footer-item">
                        <x-icon icon="delete" fill="{{config('constants.icons.color.danger')}}" />&nbsp;Sil
                    </a>

                </footer>

            </div>

            <x-card-date :item="$bina" />

        </div>

    </div>




</div>