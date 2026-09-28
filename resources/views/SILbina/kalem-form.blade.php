<x-app-layout>
    <script src="{{ asset('/js/ckeditor5/ckeditor.js') }}"></script>

    <div class="section container">

        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">{{ $kalem ? 'Harcama Kalem Tanımı Güncelle' :'Harcama Kalem Tanımı' }}</h1>
        <h2 class="subtitle">{{ $bina->name }} için harcama kalem tanımlaması</h2>

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

        <form action="{{ $kalem ? '/kalem-update/'.$bina->id.'/'.$kalem->id : '/kalem-add/'.$bina->id }}" method="POST" enctype="multipart/form-data">
        @csrf

            <div class="box">

                <div class="field">
                    <label class="label" for="name">Tanım</label>
                    <div class="control" id="name">
                        <input class="input" name="title" type="text" placeholder="Doğalgaz" value="{{ $kalem ? $kalem->title :''}}">
                    </div>
                </div>

                <div class="buttons is-right">
                    <button class="button is-link">{{ $kalem ? 'Güncelle' : 'Kaydet'}}</button>
                </div>

            </div>

        </form>
    </div>

</x-app-layout>
