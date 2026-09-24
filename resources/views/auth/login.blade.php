<x-log-layout>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" class="mx-4" action="{{ route('login') }}">
        @csrf

        <!-- Validation Errors -->
        <x-auth-validation-errors class="notification is-danger is-light" :errors="$errors" />

        <!-- Email Address -->
        <x-email name="email" :value="old('email')" />

        <!-- Password -->
        <x-password name="password" label="{{__('Password')}}" :value="old('password')" placeholder="{{__('Your Password')}}" />

        <button class="button is-link my-6 is-fullwidth">{{__('Log In')}}</button>

        <!-- Other Actions Links -->
        <x-auth-actions action="login"/>
        <a href="{{ route('resident.login') }}" class="button is-light is-fullwidth mt-3">
            Bina sakini girişi
        </a>

    </form>

</x-log-layout>
