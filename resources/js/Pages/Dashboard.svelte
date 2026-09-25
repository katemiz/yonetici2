<script>
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

    export let bina = null;
    export let auth = { user: null };
    export let selected_bina = null;

    $: user = auth?.user;
    $: userName = user ? `${user.name ?? ''} ${user.lastname ?? ''}`.trim() : '';
</script>

<svelte:head>
    <title>Gösterge Paneli</title>
</svelte:head>

<nav class="navbar is-light" aria-label="main navigation">
    <div class="container is-fluid">
        <div class="navbar-brand">
            <a href="/dashboard" class="navbar-item">
                <img src="/images/app_header_logo.svg" alt="Akıllı Yönetici">
            </a>
        </div>

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
                <div class="navbar-item has-dropdown is-hoverable">
                    <a href="/kayit-form/aidat" class="navbar-link"><SquarePen class="menu-icon" aria-hidden="true" />Kayıtlar</a>
                    <div class="navbar-dropdown">
                        <a href="/kayit-form/aidat" class="navbar-item">Toplu Aidat Kaydı</a>
                        <a href="/kayit-form/alacak" class="navbar-item">Alacak Kaydı</a>
                        <a href="/kayit-form/fatura" class="navbar-item">Fatura Kaydı</a>
                        <a href="/kayit-form/gelir" class="navbar-item">Gelir Kaydı</a>
                        <a href="/kayit-form/gider" class="navbar-item">Gider Kaydı</a>
                        <a href="/sayac-okuma" class="navbar-item">Sayaç Okumaları</a>
                    </div>
                </div>
                <div class="navbar-item has-dropdown is-hoverable">
                    <a href="/dokum" class="navbar-link"><Printer class="menu-icon" aria-hidden="true" />Yazdır</a>
                    <div class="navbar-dropdown">
                        <a href="/dokum" class="navbar-item">Gelir-Gider Döküm</a>
                        <a href="/aylik-aidatlar" class="navbar-item">Aylık Aidatlar</a>
                        <a href="/bosmakbuz" class="navbar-item">Boş Makbuz</a>
                    </div>
                </div>
            </div>

            <div class="navbar-end">
                <div class="navbar-item has-dropdown is-hoverable">
                    <a href="/bina-list" class="navbar-link user-summary">
                        <span class="is-block">{userName || 'Kullanıcı'}</span>
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
    </div>
</nav>

<main class="section">
    <div class="container">
        {#if !bina}
            <h1 class="title">Gösterge Paneli</h1>
            <div class="notification is-warning is-light">Tanımlı bir bina bulunmamaktadır.</div>
            <a href="/bina-form" class="button is-link">Bina Ekle</a>
        {:else}
            <h1 class="title">{bina.name}</h1>
            <h2 class="subtitle">Geçerli bina özellikleri</h2>
            <div class="box">
                <div class="content">
                    <p><strong>Adres:</strong> {bina.address}</p>
                    <p><strong>Şehir:</strong> {bina.city}</p>
                    <p><strong>Para birimi:</strong> {bina.pbirimi}</p>
                    <p><strong>Bina sakinleri:</strong> {bina.sakinler_count}</p>
                    <p><strong>Hizmet bedelleri:</strong> {bina.bedeller_count}</p>
                    <p><strong>Harcama türleri:</strong> {bina.kalemler_count}</p>
                    <p>
                        <strong>Ortak giriş kodu:</strong>
                        {#if bina.resident_access_code}
                            <span class="has-text-success"> Yapılandırılmış</span>
                        {:else}
                            <span class="has-text-warning-dark"> Tanımlanmamış</span>
                        {/if}
                    </p>
                </div>
                <a href={`/bina-view/${bina.id}`} class="button is-link is-light">Bina ayrıntıları</a>
            </div>
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
