<script>
    import { untrack } from 'svelte';
    import { Eye, Pencil, Plus, Search, X } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { binalar, selectedBinaId = null, search = '', sort = 'created_at', direction = 'desc', success = null } = $props();
    let query = $state(untrack(() => search));

    function sortUrl(field) {
        const params = new URLSearchParams();
        if (query) params.set('search', query);
        params.set('sort', field);
        params.set('direction', sort === field && direction === 'asc' ? 'desc' : 'asc');
        return `/bina-list?${params.toString()}`;
    }
</script>

<svelte:head>
    <title>Binalarım - Akıllı Yönetici</title>
</svelte:head>

<Layout>
    <main class="section container">
        <header class="my-6">
            <h1 class="title has-text-weight-light is-size-1">Binalarım</h1>
            <h2 class="subtitle has-text-weight-light">Yöneticiliğini yaptığım binalar</h2>
        </header>

        {#if success}
            <div class="notification is-success is-light">{success}</div>
        {/if}

        <nav class="level my-6">
            <div class="level-left">
                <div class="level-item">
                    <a href="/bina-form" class="button is-link">
                        <span class="icon is-small"><Plus size={18} /></span>
                        <span>Bina Ekle</span>
                    </a>
                </div>
            </div>
            {#if binalar.total > 0}
                <div class="level-right">
                    <div class="level-item">
                        <form method="GET" action="/bina-list" class="field has-addons">
                            <div class="control has-icons-left">
                                <input class="input" type="search" name="search" placeholder="Ara..." bind:value={query} aria-label="Binalarda ara">
                                <span class="icon is-small is-left"><Search size={16} /></span>
                            </div>
                            <input type="hidden" name="sort" value={sort}>
                            <input type="hidden" name="direction" value={direction}>
                            <div class="control"><button class="button" type="submit">Ara</button></div>
                            {#if search}
                                <div class="control"><a class="button px-1" href="/bina-list" aria-label="Aramayı temizle"><X size={18} /></a></div>
                            {/if}
                        </form>
                    </div>
                </div>
            {/if}
        </nav>

        {#if binalar.total > 0}
            <div class="table-container">
                <table class="table is-fullwidth">
                    <caption>Yönettiğiniz <b>{binalar.total}</b> bina vardır</caption>
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th><a href={sortUrl('name')}>İsim {sort === 'name' ? (direction === 'asc' ? '↑' : '↓') : ''}</a></th>
                            <th><a href={sortUrl('created_at')}>Eklenme Zamanı {sort === 'created_at' ? (direction === 'asc' ? '↑' : '↓') : ''}</a></th>
                            <th class="has-text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each binalar.data as bina}
                            <tr class:is-selected={Number(selectedBinaId) === Number(bina.id)}>
                                <td><a href={`/select-active/${bina.id}`}>Seç</a></td>
                                <td><a href={`/bina-view/${bina.id}`}>{bina.name}</a></td>
                                <td>{bina.created_at}</td>
                                <td class="has-text-right">
                                    <a href={`/bina-view/${bina.id}`} class="icon" aria-label="Görüntüle"><Eye size={18} /></a>
                                    <a href={`/bina-form/${bina.id}`} class="icon" aria-label="Düzenle"><Pencil size={18} /></a>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            {#if binalar.links?.length > 3}
                <nav class="pagination is-centered" aria-label="Sayfalar">
                    {#each binalar.links as link}
                        {#if link.url}
                            <a class:is-current={link.active} class="pagination-link" href={link.url} aria-label={link.label}>{@html link.label}</a>
                        {/if}
                    {/each}
                </nav>
            {/if}
        {:else}
            <div class="notification is-warning is-light">Yönettiğiniz bina bulunmamaktadır.</div>
        {/if}
    </main>
</Layout>

<style>
    :global(.pagination-link.is-current) {
        background-color: hsl(217, 71%, 35%);
        border-color: hsl(217, 71%, 35%);
    }
</style>
