<x-log-layout>
    <form method="POST" class="mx-4" action="{{ route('resident.login.store') }}">
        @csrf

        <x-auth-validation-errors class="notification is-danger is-light" :errors="$errors" />

        <div class="field">
            <label class="label" for="phone">Telefon numarası</label>
            <div class="control">
                <input id="phone" name="phone" class="input" type="tel"
                    value="{{ old('phone') }}" placeholder="+90 5xx xxx xx xx" required>
            </div>
        </div>

        <div class="field">
            <label class="label" for="building_code">Bina giriş kodu</label>
            <div class="control">
                <input id="building_code" name="building_code" class="input" type="password"
                    autocomplete="current-password" required>
            </div>
        </div>

        <button class="button is-link my-6 is-fullwidth">Giriş yap</button>
        <a href="{{ route('login') }}" class="button is-light is-fullwidth">Yönetici girişi</a>
    </form>
</x-log-layout>
