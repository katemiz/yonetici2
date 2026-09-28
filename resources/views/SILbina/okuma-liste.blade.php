
<x-app-layout>

<div class="section container">

    <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">Sayaçlı Okumalar Geçmişi</h1>
    <h2 class="subtitle">Sayaçlı Okumalar Geçmişi</h2>

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


    @if (count($okumali_bedeller) > 0)
    @foreach ($okumali_bedeller as $tur)

        <h1 class="title my-6 has-text-weight-light is-size-2 has-text-centered">{{$tur->title}}</h1>

        @if (count($bina->sakinler) > 0)
        <table class="table is-fullwidth">


        @foreach ($bina->sakinler as $sakin)


                <tr>
                    <th>{{$sakin->door_no}}</th>

                    <th>{{$sakin->name}} {{$sakin->lastname}}</th>



                    <td>
                        @if (count($okumalar[$sakin->id]) > 0)

                        <table class="table is-fullwidth">

                        @foreach ($okumalar[$sakin->id] as $okuma)

                            <tr>
                                <td>{{$okuma->son_okuma}}</td>
                                <td>{{$okuma->created_at}}</td>
                            </tr>

                        @endforeach
                        </table>
                        @else
                            <div class="notification is-warning is-light">
                            Okuma değeri yoktur.
                            </div>
                        @endif

                    </td>
                </tr>

        @endforeach
    </table>


        @else

            <div class="notification is-warning is-light">
            Bina sakini tanımı yoktur.
            </div>

        @endif

    @endforeach
    @else
        <div class="notification is-warning is-light">
        Sayaçlı okumaya dayalı bir hizmet/ürün bulunmamaktadır.
        </div>
    @endif

</div>
</x-app-layout>
