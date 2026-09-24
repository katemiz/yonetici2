<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('constants.app.name', 'Bina Yönetim Sistemi') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset(Config::get('constants.favicon')) }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-shell">
        @inertia

        <footer class="footer has-background-light">
            <div class="columns">
                <div class="column has-text-left has-text-centered-mobile">
                    <img src="{{ asset('images/' . config('constants.company.logo')) }}"
                        width="28" alt="{{ config('constants.company.name') }}">
                    <br>
                    <a href="{{ config('constants.company.link') }}" class="has-text-weight-light">
                        {{ config('constants.company.name') }}
                    </a>
                    <p class="has-text-weight-light is-size-7">
                        {{ config('constants.company.motto') }}
                    </p>
                </div>

                <div class="column has-text-centered has-text-centered-mobile">
                    {{ config('constants.app.title') }}
                </div>

                <div class="column has-text-right has-text-centered-mobile">
                    {{ config('constants.app.copyright') }}<br>
                    <span class="is-size-7 has-text-grey">
                        {{ config('constants.app.version') }}
                    </span>
                </div>
            </div>
        </footer>
    </body>
</html>
