<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $sectionLabel }} - {{ $building->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <nav class="navbar is-light">
        <div class="container is-fluid">
            <a href="{{ route('resident.dashboard') }}" class="navbar-item">
                <img src="{{ asset('images/app_header_logo.svg') }}" alt="Akıllı Yönetici">
            </a>
            <div class="navbar-end">
                <a href="{{ route('resident.status', 'ozet') }}" class="navbar-item">Genel Durum</a>
                <a href="{{ route('resident.status', 'alacaklar') }}" class="navbar-item">Alacaklar</a>
                <a href="{{ route('resident.status', 'gelirler') }}" class="navbar-item">Gelirler</a>
                <a href="{{ route('resident.status', 'giderler') }}" class="navbar-item">Giderler</a>
                <form method="POST" action="{{ route('resident.logout') }}" class="navbar-item">
                    @csrf
                    <button type="submit" class="button is-light">Çıkış</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="section">
        <div class="container">
            <h1 class="title">{{ $sectionLabel }}</h1>
            <h2 class="subtitle">{{ $building->name }}</h2>
            <div class="notification is-info is-light">Bu alan yalnızca görüntüleme içindir.</div>

            @if ($summary)
                <div class="columns is-multiline">
                    @foreach ([
                        'gelirler' => 'Toplam Gelir',
                        'giderler' => 'Toplam Gider',
                        'nakit' => 'Bakiye',
                        'alacaklar' => 'Toplam Alacaklar',
                        'verecekler' => 'Toplam Borçlar',
                    ] as $key => $label)
                        <div class="column is-one-fifth-desktop is-half-tablet">
                            <div class="box has-text-centered">
                                <p class="heading">{{ $label }}</p>
                                <p class="title is-5">{{ number_format($summary[$key], 2, ',', ' ') }} {{ $building->pbirimi }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @if ($records->count() === 0)
                    <div class="notification is-warning is-light">Bu bölümde kayıt bulunmamaktadır.</div>
                @else
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Açıklama</th>
                                    @if ($section === 'alacaklar' || $section === 'gelirler')
                                        <th>Kapı No</th>
                                    @endif
                                    <th class="has-text-right">Tutar</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($records as $record)
                                    <tr>
                                        <td>{{ $record->aciklama }}</td>
                                        @if ($section === 'alacaklar' || $section === 'gelirler')
                                            <td>{{ $record->sakin?->door_no ?? '-' }}</td>
                                        @endif
                                        <td class="has-text-right">{{ number_format($record->tutar, 2, ',', ' ') }} {{ $building->pbirimi }}</td>
                                        <td>{{ optional($record->created_at)->format('d.m.Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $records->links() }}
                @endif
            @endif
        </div>
    </main>

    <footer class="footer has-background-light">
        <div class="has-text-centered">{{ config('constants.app.title') }}</div>
    </footer>
</body>
</html>
