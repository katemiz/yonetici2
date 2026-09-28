<script>
    import { untrack } from 'svelte';
    import { ArrowDown, ArrowLeft, ArrowUp, Pencil, Plus, Search, X } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, sakinler, search = '', sort = 'created_at', direction = 'desc' } = $props();
    let query = $state(untrack(() => search));

    function sortUrl(field) {
        const params = new URLSearchParams();
        if (query) params.set('search', query);
        params.set('sort', field);
        params.set('direction', sort === field && direction === 'asc' ? 'desc' : 'asc');
        return `/sakin-list/${bina.id}?${params.toString()}`;
    }
</script>

<svelte:head>
    <title>Bina Sakinleri - {bina.name}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <header class="my-6">
            <h1 class="title has-text-weight-light is-size-1">Bina Sakinleri</h1>
            <h2 class="subtitle has-text-weight-light">{bina.name} oturan sakinler</h2>
        </header>

        <a href={`/bina-view/${bina.id}`} class="icon-text">
            <span class="icon"><ArrowLeft size={18} /></span>
            <span>Geri</span>
        </a>

        <nav class="level my-6">
            <div class="level-left">
                <a href={`/sakin-form/${bina.id}`} class="button is-link">
                    <span class="icon is-small"><Plus size={18} /></span>
                    <span>Bina Sakini Ekle</span>
                </a>
            </div>
            {#if sakinler.total > 0}
                <div class="level-right">
                    <form method="GET" action={`/sakin-list/${bina.id}`} class="field has-addons">
                        <div class="control has-icons-left">
                            <input class="input" type="search" name="search" placeholder="Ara..." bind:value={query} aria-label="Sakinlerde ara">
                            <span class="icon is-small is-left"><Search size={16} /></span>
                        </div>
                        <input type="hidden" name="sort" value={sort}>
                        <input type="hidden" name="direction" value={direction}>
                        <div class="control"><button class="button" type="submit">Ara</button></div>
                        {#if search}
                            <div class="control">
                                <a class="button px-1" href={`/sakin-list/${bina.id}`} aria-label="Aramayı temizle">
                                    <X size={18} />
                                </a>
                            </div>
                        {/if}
                    </form>
                </div>
            {/if}
        </nav>

        {#if sakinler.total > 0}
            <div class="table-container">
                <table class="table is-fullwidth">
                    <caption>Bu yerleşkede <b>{sakinler.total}</b> sakin oturmaktadır</caption>
                    <thead>
                        <tr>
                            <th>
                                <a class="icon-text" href={sortUrl('name')}>
                                    {#if sort === 'name'}
                                        {#if direction === 'asc'}<ArrowUp size={16} />{:else}<ArrowDown size={16} />{/if}
                                    {/if}
                                    <span>İsim</span>
                                </a>
                            </th>
                            <th>
                                <a class="icon-text" href={sortUrl('created_at')}>
                                    {#if sort === 'created_at'}
                                        {#if direction === 'asc'}<ArrowUp size={16} />{:else}<ArrowDown size={16} />{/if}
                                    {/if}
                                    <span>Eklenme Zamanı</span>
                                </a>
                            </th>
                            <th class="has-text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each sakinler.data as sakin}
                            <tr>
                                <td><a href={`/sakin-view/${bina.id}/${sakin.id}`}>{sakin.name} {sakin.lastname}</a></td>
                                <td>{sakin.created_at}</td>
                                <td class="has-text-right">
                                    <a href={`/sakin-form/${bina.id}/${sakin.id}`} class="icon" aria-label="Düzenle">
                                        <Pencil size={18} />
                                    </a>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            {#if sakinler.links?.length > 3}
                <nav class="pagination is-centered" aria-label="Sayfalar">
                    {#each sakinler.links as link}
                        {#if link.url}
                            <a class:is-current={link.active} class="pagination-link" href={link.url} aria-label={link.label}>
                                {@html link.label}
                            </a>
                        {/if}
                    {/each}
                </nav>
            {/if}
        {:else}
            <div class="notification is-warning is-light">
                {search ? 'Aramanızla eşleşen sakin bulunamadı.' : 'Bu bina/yerleşke için oturan tanımı yapılmamıştır.'}
            </div>
        {/if}
    </main>
</Layout>

<style>
    :global(.pagination-link.is-current) {
        background-color: hsl(217, 71%, 35%);
        border-color: hsl(217, 71%, 35%);
    }
</style>
