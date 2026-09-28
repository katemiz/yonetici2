<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="loghtml">

<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset(Config::get('constants.favicon')) }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ Config::get('constants.app.title') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>


<body class="logbody">

    <section class="section container is-max-desktop">

        <div class="column is-half is-offset-one-quarter">

            <nav class="breadcrumb has-bullet-separator is-right" aria-label="breadcrumbs">
                <ul>
                    <li class="{{ app()->getLocale() == 'tr' ? 'is-active' : ''}}">
                        <a href="{{ route('lang.switch', 'tr') }}">TR</a>
                    </li>
                    <li class="{{ app()->getLocale() == 'en' ? 'is-active' : ''}}">
                        <a href="{{ route('lang.switch', 'en') }}">EN</a>
                    </li>
                </ul>
            </nav>

        </div>

        <div class="column is-half is-offset-one-quarter has-background-white">

            <div class="column is-offset-3 is-offset-4-mobile is-6 is-4-mobile my-6">
                <figure class="image">
                    <img src="/images/{{config('constants.app.app_login_logo')}}"
                        alt="{{config('constants.app.name')}}">
                </figure>
            </div>

            {{$slot}}

        </div>

        <div class="column is-half is-offset-one-quarter">

            <a href="{{config('constants.company.link')}}" class="button is-small is-ghost">
                <span class="icon is-small">
                    <img src="/images/{{config('constants.company.logo')}}" alt="{{config('constants.company.name')}}">
                </span>
                <span>{{config('constants.company.name')}}</span>
            </a>

        </div>

    </section>
</body>

</html>