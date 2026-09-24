<script>
    export let bina_sayisi = 0;
    export let bina = null;
</script>

<svelte:head>
    <title>Gösterge Paneli</title>
</svelte:head>

<nav class="navbar is-light" aria-label="main navigation">
    <div class="container is-fluid">
        <a href="/dashboard" class="navbar-item">
            <img src="/images/app_header_logo.svg" alt="Akıllı Yönetici">
        </a>
        <div class="navbar-end">
            <a href="/bina-list" class="navbar-item">Binalarım</a>
            <form method="POST" action="/logout" class="navbar-item">
                <input type="hidden" name="_token" value={document.querySelector('meta[name=csrf-token]').content}>
                <button class="button is-text" type="submit">Çıkış</button>
            </form>
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
