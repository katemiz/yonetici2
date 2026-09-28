<div class="section container">

    <header class="my-6">
        <h1 class="title has-text-weight-light is-size-1">Sayaç Okumaları</h1>
        <h2 class="subtitle has-text-weight-light">Yeni Sayaç Okuma</h2>
    </header>



    @foreach ($sakinler as $sakin)

        <div class="box mb-4">

            <div class="fixed-grid">
                <div class="grid ">

                    <div class="cell">
                        <h3 class="title is-4">{{ $sakin['door_no'] }} - {{ $sakin['name'] }} {{ $sakin['lastname'] }}</h3>
                    </div>

                    <div class="cell has-text-right">
                        <button wire:click="yeniOkuma({{ $sakin['id'] }})" class="button is-link">
                            <span class="icon">
                                <x-icon icon="plus" />
                            </span>
                            <span>Sayaç Okuma Ekle</span>
                        </button>
                    </div>

                </div>
            </div>

            <table class="table is-fullwidth is-striped is-hoverable mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Okuma Tarihi</th>
                        <th class="has-text-right">Okuma Değeri</th>
                        <th>Fatura Durumu</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($okumalar[$sakin['id']] as $key => $okuma)
                        <tr>
                            <td>{{ count($okumalar[$sakin['id']]) - $key }}</td>
                            <td>{{ \Carbon\Carbon::parse($okuma['created_at'])->format('d.m.Y') }}</td>
                            <td class="has-text-right is-size-6 has-text-weight-bold">
                                {{ sprintf("%4d", $okuma['okuma_degeri']) }}
                            </td>
                            <td>{{ $okuma['status'] }}</td>
                            <td>
                                @if ($okuma['kayit_id'] < 1)
                                    <button class="button is-link is-light">Fatura Kaydı Yap</button>
                                @else
                                    <a href="/kayit-gor/{{ $okuma['kayit_id'] }}">Kayıda Bak</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    @endforeach



    <div class="modal {{ $okumaEkleModal ? 'is-active' : '' }}">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Yeni Saya Okuma</p>
                <button class="delete" aria-label="close"></button>
            </header>
            <section class="modal-card-body">
                <!-- Content ... -->
            </section>
            <footer class="modal-card-foot">
                <div class="buttons">
                    <button class="button is-success">Save changes</button>
                    <button class="button">Cancel</button>
                </div>
            </footer>
        </div>
    </div>

</div>