<div class="section container">

    <script>
        function showModal(idSakin,idBedel,isim,kapiNo,bedelIsmi) {

            let baslik = '['+kapiNo+' - '+isim+'] '+bedelIsmi+ ' Okumaları'

            document.getElementById('baslik').innerHTML = baslik
            document.getElementById('sayac_modal').classList.add('is-active')

            window.livewire.emit('getReadings', idSakin,idBedel)
        }


        function hideModal() {
            document.getElementById('sayac_modal').classList.remove('is-active');
        }
    </script>

    <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">Sayaçlı Okumalar</h1>
    <h2 class="subtitle">Sayaca Bağlı Okuma Değerleri</h2>


    {{-- <button onclick="Livewire.emit('openModal', 'hello-modal')"
    class="px-3 py-2 text-sm border border-gray-200 rounded-md">Open Modal</button> --}}


    @foreach ($sakinler as $sakin)
    <div class="columns box">

        <div class="column is-1 has-text-weight-bold is-size-3 has-background-grey-light">{{$sakin->door_no}}</div>
        <div class="column is-2">{{$sakin->name}} {{$sakin->lastname}}</div>

        @foreach ($okumali_bedeller as $bedel)
        <div class="column has-text-centered">
            <a class="control" onclick="showModal({{$sakin->id}},{{$bedel->id}},'{{$sakin->name}} {{$sakin->lastname}}','{{$sakin->door_no}}','{{ $bedel->title }}')">
                <span>{{ $bedel->title }}<br>Okunan Değerler</span>

            </a>
        </div>
        @endforeach

    </div>
    @endforeach


    <div class="modal {{$show_modal ? 'is-active': ''}}" id="sayac_modal">
        <div class="modal-background"  onclick="hideModal()"></div>
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title" id="baslik">Başlık</p>
            <button class="delete" aria-label="close" onclick="hideModal()"></button>
          </header>
          <section class="modal-card-body">
            @if (!$readings)
              <div class="notification is-warning">Okuma bulunmamaktadır</div>
            @else
              @foreach ($readings as $reading)
                <p>{{$reading->son_okuma}}</p>
              @endforeach
            @endif
          </section>
        </div>
    </div>

</div>
