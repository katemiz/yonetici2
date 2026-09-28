<script>
    import Layout from './Shared/Layout.svelte';

    let { bina, totals = null, userType = 'guest' } = $props();
    let currency = $derived(bina?.pbirimi ?? '');

    const summaryItems = [
        ['TOPLAM GELİR', 'gelir'],
        ['TOPLAM GİDER', 'gider'],
        ['BAKİYE', 'nakit'],
        ['TOPLAM ALACAKLAR', 'alacak'],
        ['TOPLAM BORÇLAR', 'verecek']
    ];
</script>

<svelte:head>
    <title>Genel Durum - {bina?.name ?? 'Akıllı Yönetici'}</title>
</svelte:head>

<Layout>
    <main class="section">
        <div class="container">
            <header class="my-6">
                <h1 class="title has-text-weight-light is-size-1">Genel Durum</h1>
                <h2 class="subtitle has-text-weight-light">{bina?.name}</h2>
            </header>

            {#if totals}
                <div class="columns is-multiline my-6">
                    {#each summaryItems as [label, key], index}
                        <div class={index < 3 ? 'column is-one-third' : 'column is-half'}>
                            <div class={index < 3 ? 'box has-background-grey-light has-text-centered' : 'box has-background-light has-text-centered'}>
                                <p class="heading">{label}</p>
                                <p class:has-text-link={index < 3} class="title">{totals[key]} {currency}</p>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}

            {#if userType === 'resident'}
                <div class="notification is-info is-light">
                    Bu alan yalnızca görüntüleme içindir.
                </div>
            {/if}
        </div>
    </main>
</Layout>
