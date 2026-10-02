<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />

  <link rel="icon" type="image/svg+xml" href="{{ asset(Config::get('constants.favicon')) }}" />

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>{{ config('constants.app.name', 'Bina Yönetim Sistemi') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  @livewireStyles

</head>

<body>

  @include('layouts.navigation', ["user" => false])

  {{ $slot }}

  @include('layouts.footer')

  @livewireScripts

</body>

</html>