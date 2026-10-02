<script>
    import {  X } from '@lucide/svelte';
    import { page, router } from '@inertiajs/svelte';
    import { Eye, Pencil, Plus } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';
    import SearchBox from './components/SearchBox.svelte';

    let { binalar, selectedBinaId = null, query = '', sort = 'created_at', direction = 'desc', success = null } = $props();
    
    let quota = $derived(page?.props?.buildingQuota ?? null);
    
    let clearSearchIcon = $state(false);

    $effect(() => {
        if (query) {
            clearSearchIcon = true;

            if (query && query.length > 2) {
                doSearch();
            }
        } else {
            clearSearchIcon = false;
            doSearch();
        }
    });

    function doSearch() {
        router.get(
            "/bina-list",
            {
                query: query,
            },
            {
                preserveState: true,
                replace: true, // Prevents flooding browser history with every single keystroke
                preserveScroll: true,
            },
        );
    }

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
                    {#if !quota || quota.used < quota.limit}
                        <a href="/bina-form" class="button is-link">
                            <span class="icon is-small"><Plus size={18} /></span>
                            <span>Bina Ekle</span>
                        </a>
                    {:else}
                        <p class="has-text-warning-dark">Bina kotanız dolu ({quota.used}/{quota.limit}).</p>
                    {/if}
                </div>
            </div>
                <div class="level-right">
                    <div class="level-item">
                        <SearchBox bind:query={query} placeholder="Ara..." />
                    </div>
                </div>
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
