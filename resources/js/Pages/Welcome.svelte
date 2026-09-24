<script>
    import { Link } from '@inertiajs/svelte';

    export let auth = { user: null };
    export let selected_bina = null;

    $: user = auth?.user;
    $: userName = user ? `${user.name ?? ''} ${user.lastname ?? ''}`.trim() : '';
</script>

<svelte:head>
    <title>Akıllı Yönetici</title>
</svelte:head>

<nav class="navbar is-light" aria-label="main navigation">
    <div class="container is-fluid">
        <div class="navbar-brand">
            <Link href="/" class="navbar-item">
                <img src="/images/app_header_logo.svg" alt="Akıllı Yönetici">
            </Link>
        </div>
        {#if user}
            <div class="navbar-menu is-active">
                <div class="navbar-start">
                    <a href="/dashboard" class="navbar-item">Gösterge Paneli</a>
                    <a href="/durum/gelirler" class="navbar-item">Gelir</a>
                    <a href="/durum/giderler" class="navbar-item">Gider</a>
                    <a href="/durum/verecekler" class="navbar-item">Faturalar</a>
                    <a href="/kayit-form/aidat" class="navbar-item">Kayıtlar</a>
                    <a href="/dokum" class="navbar-item">Yazdır</a>
                </div>
                <div class="navbar-end">
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a href="/bina-list" class="navbar-link user-summary">
                            <span class="is-block">{userName}</span>
                            {#if selected_bina}
                                <small class="is-block">{selected_bina}</small>
                            {/if}
                        </a>
                        <div class="navbar-dropdown is-right">
                            <a href="/bina-list" class="navbar-item">Binalarım</a>
                            <a href="/help" class="navbar-item">Yardım</a>
                            <form method="POST" action="/logout">
                                <input type="hidden" name="_token" value={document.querySelector('meta[name=csrf-token]').content}>
                                <button class="navbar-item button is-white is-fullwidth has-text-left" type="submit">
                                    Çıkış
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        {:else}
            <div class="navbar-end">
                <a href="/login" class="navbar-item">Giriş</a>
                <a href="/register" class="navbar-item">Kaydolun</a>
            </div>
        {/if}
    </div>
</nav>

<main class="section">
    <div class="container">
        <div class="columns is-vcentered">
            <div class="column">
                <h1 class="title is-size-1">Akıllı Yönetici<br>Akıllı Uygulama</h1>
                <p class="subtitle">Tüm işlemlerinizi cepten yönetin.</p>
                <p>Site ve apartman yönetiminizi; aidat, gelir-gider, fatura ve karar kayıtlarını tek bir uygulamadan takip edin.</p>
                {#if user && selected_bina}
                    <div class="notification is-success is-light mt-5">
                        <strong>Geçerli bina:</strong> {selected_bina}
                    </div>
                {/if}
                {#if !user}
                    <div class="buttons mt-5">
                        <a href="/login" class="button is-link">Giriş yap</a>
                        <a href="/register" class="button is-light">Hesap oluştur</a>
                    </div>
                {/if}
            </div>
            <div class="column">
                <figure class="image">
                    <img src="/images/hero.svg" alt="Bina yönetimi iş akışı">
                </figure>
            </div>
        </div>
    </div>
</main>

<style>
    :global(.user-summary) {
        align-items: flex-end;
        flex-direction: column;
        justify-content: center;
    }
</style>
