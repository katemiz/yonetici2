<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('constants.app.name', 'Bina Yönetim Sistemi') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <nav class="navbar is-light">
        <div class="container is-fluid">
            <a href="{{ route('resident.dashboard') }}" class="navbar-item">
                <img src="{{ asset('images/app_header_logo.svg') }}" alt="Akıllı Yönetici">
            </a>
            <div class="navbar-end">
                <span class="navbar-item">{{ $resident->name }} {{ $resident->lastname }}</span>
                <form method="POST" action="{{ route('resident.logout') }}" class="navbar-item">
                    @csrf
                    <button type="submit" class="button is-light">Çıkış</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="section">
        <div class="container">
            <h1 class="title">Hoşgeldiniz, {{ $resident->name }}</h1>
            <h2 class="subtitle">{{ $building->name }}</h2>
            <div class="notification is-info is-light">
                Bu alan yalnızca görüntüleme içindir.
            </div>
            <div class="buttons">
                <a href="{{ route('resident.status', 'ozet') }}" class="button is-link is-light">Genel Durum</a>
                <a href="{{ route('resident.status', 'alacaklar') }}" class="button is-link is-light">Alacaklar</a>
                <a href="{{ route('resident.status', 'gelirler') }}" class="button is-link is-light">Gelirler</a>
                <a href="{{ route('resident.status', 'giderler') }}" class="button is-link is-light">Giderler</a>
            </div>
            <div class="box">
                <p><strong>Telefon:</strong> {{ $resident->phone }}</p>
                <p><strong>Kapı No:</strong> {{ $resident->door_no }}</p>
                <p><strong>Adres:</strong> {{ $building->address }}</p>
            </div>
        </div>
    </main>

    <footer class="footer has-background-light">
        <div class="has-text-centered">{{ config('constants.app.title') }}</div>
    </footer>
</body>
</html>
