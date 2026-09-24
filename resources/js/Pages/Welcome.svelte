<script>
    import { Link } from '@inertiajs/svelte';
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
        <div class="navbar-end">
            <a href="/login" class="navbar-item">Giriş</a>
            <a href="/register" class="navbar-item">Kaydolun</a>
        </div>
    </div>
</nav>

<main class="section">
    <div class="container">
        <div class="columns is-vcentered">
            <div class="column">
                <h1 class="title is-size-1">Akıllı Yönetici<br>Akıllı Uygulama</h1>
                <p class="subtitle">Tüm işlemlerinizi cepten yönetin.</p>
                <p>Site ve apartman yönetiminizi; aidat, gelir-gider, fatura ve karar kayıtlarını tek bir uygulamadan takip edin.</p>
                <div class="buttons mt-5">
                    <a href="/login" class="button is-link">Giriş yap</a>
                    <a href="/register" class="button is-light">Hesap oluştur</a>
                </div>
            </div>
            <div class="column">
                <figure class="image">
                    <img src="/images/hero.svg" alt="Bina yönetimi iş akışı">
                </figure>
            </div>
        </div>
    </div>
</main>
