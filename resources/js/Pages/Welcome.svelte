<script>
    import Layout from './Shared/Layout.svelte';

    let { auth = { user: null }, bina_sayisi = 0, bina = null } = $props();
    let user = $derived(auth?.user);
</script>

<svelte:head>
    <title>Akıllı Yönetici</title>
</svelte:head>

<Layout>
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
                        {:else if bina_sayisi === 0}
                            <section class="mt-6">
                                <div class="notification is-warning is-light">Tanımlı bir bina bulunmamaktadır.</div>
                                <a href="/bina-form" class="button is-link">Bina Ekle</a>
                            </section>
                        {:else if bina}
                            <section class="mt-6">
                                <div class="notification is-success is-light">
                                    <h2 class="title is-4">{bina.name}</h2>
                                    {bina.address}, {bina.city}
                                </div>
                            </section>
                        {:else}
                            <section class="mt-6">
                                <div class="notification is-info is-light">Bir bina seçmek için Binalarım sayfasını açın.</div>
                                <a href="/bina-list" class="button is-link">Binalarım</a>
                            </section>
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
</Layout>
