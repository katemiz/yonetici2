<script>
    import Layout from './Shared/Layout.svelte';

    let { bina, totals } = $props();
    let currency = $derived(bina?.pbirimi ?? '');
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

            <div class="columns is-multiline my-6">
                {#each [
                    ['TOPLAM GELİR', totals.gelir],
                    ['TOPLAM GİDER', totals.gider],
                    ['BAKİYE', totals.nakit]
                ] as [label, value]}
                    <div class="column is-one-third">
                        <div class="box has-background-grey-light has-text-centered">
                            <p class="heading">{label}</p>
                            <p class="title has-text-link">{value} {currency}</p>
                        </div>
                    </div>
                {/each}
            </div>

            <div class="columns my-6">
                {#each [
                    ['TOPLAM ALACAKLAR', totals.alacak],
                    ['TOPLAM BORÇLAR', totals.verecek]
                ] as [label, value]}
                    <div class="column is-half">
                        <div class="box has-background-light has-text-centered">
                            <p class="heading">{label}</p>
                            <p class="title">{value} {currency}</p>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </main>
</Layout>
