<script>
    import { Link } from '@inertiajs/svelte';
    import {
        Banknote,
        Building2,
        ChartBar,
        CircleHelp,
        FileText,
        LogOut,
        Printer,
        SquarePen,
    } from 'lucide-svelte';

    export let auth = { user: null };
    export let selected_bina = null;
    export let bina_sayisi = 0;
    export let bina = null;

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
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a href="/durum/ozet" class="navbar-link"><ChartBar class="menu-icon" aria-hidden="true" />Durum</a>
                        <div class="navbar-dropdown">
                            <a href="/durum/ozet" class="navbar-item">Genel Özet</a>
                            <a href="/durum/alacaklar" class="navbar-item">Alacaklar</a>
                            <a href="/durum/verecekler" class="navbar-item">Verecekler</a>
                        </div>
                    </div>
                    <a href="/durum/gelirler" class="navbar-item"><Banknote class="menu-icon" aria-hidden="true" />Gelir</a>
                    <a href="/durum/giderler" class="navbar-item"><Banknote class="menu-icon" aria-hidden="true" />Gider</a>
                    <a href="/durum/verecekler" class="navbar-item"><FileText class="menu-icon" aria-hidden="true" />Faturalar</a>
                    <a href="/kayit-form/aidat" class="navbar-item"><SquarePen class="menu-icon" aria-hidden="true" />Kayıtlar</a>
                    <a href="/dokum" class="navbar-item"><Printer class="menu-icon" aria-hidden="true" />Yazdır</a>
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
                            <a href="/bina-list" class="navbar-item"><Building2 class="menu-icon" aria-hidden="true" />Binalarım</a>
                            <a href="/help" class="navbar-item"><CircleHelp class="menu-icon" aria-hidden="true" />Yardım</a>
                            <form method="POST" action="/logout">
                                <input type="hidden" name="_token" value={document.querySelector('meta[name=csrf-token]').content}>
                                <button class="navbar-item button is-white is-fullwidth has-text-left" type="submit">
                                    <LogOut class="menu-icon" aria-hidden="true" />Çıkış
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
        {#if user}
            <section class="mt-6">
                <h2 class="title is-4">Geçerli bina</h2>
                {#if bina_sayisi === 0}
                    <div class="notification is-warning is-light">Tanımlı bir bina bulunmamaktadır.</div>
                    <a href="/bina-form" class="button is-link">Bina Ekle</a>
                {:else if bina}
                    <div class="box">
                        <h3 class="title is-5">{bina.name}</h3>
                        <p>Adres: {bina.address}, {bina.city}</p>
                        <p class="mt-2">Bina sakinleri, bedeller ve harcamaları yönetebilirsiniz.</p>
                        <a href={`/bina-view/${bina.id}`} class="button is-link is-light mt-4">Bina özelliklerini görüntüle</a>
                    </div>
                {:else}
                    <div class="notification is-info is-light">Bir bina seçmek için Binalarım sayfasını açın.</div>
                    <a href="/bina-list" class="button is-link">Binalarım</a>
                {/if}
            </section>
        {/if}
    </div>
</main>

<style>
    :global(.user-summary) {
        align-items: flex-end;
        flex-direction: column;
        justify-content: center;
    }

    :global(.menu-icon) {
        color: hsl(217, 71%, 35%);
        font-size: 1.15em;
        margin-right: 0.4rem;
        width: 1.2em;
        height: 1.2em;
        flex: 0 0 auto;
    }
</style>
