<script>
    export let bina_sayisi = 0;
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
                    <a href="/durum/ozet" class="navbar-link"><i class="fa-solid fa-chart-line mr-1" aria-hidden="true"></i>Durum</a>
                    <div class="navbar-dropdown">
                        <a href="/durum/ozet" class="navbar-item">Genel Özet</a>
                        <a href="/durum/alacaklar" class="navbar-item">Alacaklar</a>
                        <a href="/durum/verecekler" class="navbar-item">Verecekler</a>
                    </div>
                </div>
                <a href="/durum/gelirler" class="navbar-item"><i class="fa-solid fa-coins mr-1" aria-hidden="true"></i>Gelir</a>
                <a href="/durum/giderler" class="navbar-item"><i class="fa-solid fa-money-bill-transfer mr-1" aria-hidden="true"></i>Gider</a>
                <a href="/durum/verecekler" class="navbar-item"><i class="fa-solid fa-file-invoice mr-1" aria-hidden="true"></i>Faturalar</a>
                <div class="navbar-item has-dropdown is-hoverable">
                    <a href="/kayit-form/aidat" class="navbar-link"><i class="fa-solid fa-pen-to-square mr-1" aria-hidden="true"></i>Kayıtlar</a>
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
                    <a href="/dokum" class="navbar-link"><i class="fa-solid fa-print mr-1" aria-hidden="true"></i>Yazdır</a>
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
                        <a href="/bina-list" class="navbar-item"><i class="fa-solid fa-building mr-1" aria-hidden="true"></i>Binalarım</a>
                        <a href="/help" class="navbar-item"><i class="fa-solid fa-circle-question mr-1" aria-hidden="true"></i>Yardım</a>
                        <form method="POST" action="/logout">
                            <input type="hidden" name="_token" value={document.querySelector('meta[name=csrf-token]').content}>
                            <button class="navbar-item button is-white is-fullwidth has-text-left" type="submit">
                                <i class="fa-solid fa-right-from-bracket mr-1" aria-hidden="true"></i>Çıkış
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
        <h1 class="title">Hoşgeldiniz</h1>
        {#if bina_sayisi === 0}
            <div class="notification is-warning is-light">Tanımlı bir bina bulunmamaktadır.</div>
            <a href="/bina-form" class="button is-link">Bina Ekle</a>
        {:else}
            <div class="notification is-success is-light">
                {bina?.name ?? 'Geçerli bina'}
            </div>
            <p>Geçerli binanızın sakin, bedel ve harcama kayıtlarını yönetebilirsiniz.</p>
        {/if}
    </div>
</main>

<style>
    :global(.user-summary) {
        align-items: flex-end;
        flex-direction: column;
        justify-content: center;
    }
</style>
