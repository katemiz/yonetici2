<script>
    import { untrack } from 'svelte';
    import { ArrowDown, ArrowLeft, ArrowUp, Pencil, Plus, Search, Trash2, X } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let {
        bina,
        kalemler,
        search = '',
        sort = 'created_at',
        direction = 'desc',
        success = null
    } = $props();
    let query = $state(untrack(() => search));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let deletingId = $state(null);

    function sortUrl(field) {
        const params = new URLSearchParams();
        if (query) params.set('search', query);
        params.set('sort', field);
        params.set('direction', sort === field && direction === 'asc' ? 'desc' : 'asc');
        return `/kalem-list/${bina.id}?${params.toString()}`;
    }
</script>

<svelte:head>
    <title>Harcama Kalemleri - {bina.name}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <header class="my-6">
            <h1 class="title has-text-weight-light is-size-1">Harcama Kalemleri</h1>
            <h2 class="subtitle has-text-weight-light">{bina.name} için harcama/fatura kalemleri</h2>
        </header>

        <a href={`/bina-view/${bina.id}`} class="icon-text">
            <span class="icon"><ArrowLeft size={18} /></span><span>Geri</span>
        </a>

        {#if success}
            <div class="notification is-success is-light">{success}</div>
        {/if}

        <nav class="level my-6">
            <div class="level-left">
                <a href={`/kalem-form/${bina.id}`} class="button is-link">
                    <span class="icon is-small"><Plus size={18} /></span><span>Kalem Ekle</span>
                </a>
            </div>
            {#if kalemler.total > 0}
                <div class="level-right">
                    <form method="GET" action={`/kalem-list/${bina.id}`} class="field has-addons">
                        <div class="control has-icons-left">
                            <input class="input" type="search" name="search" placeholder="Ara..." bind:value={query} aria-label="Harcama kalemlerinde ara">
                            <span class="icon is-small is-left"><Search size={16} /></span>
                        </div>
                        <input type="hidden" name="sort" value={sort}>
                        <input type="hidden" name="direction" value={direction}>
                        <div class="control"><button class="button" type="submit">Ara</button></div>
                        {#if search}
                            <div class="control"><a class="button px-1" href={`/kalem-list/${bina.id}`} aria-label="Aramayı temizle"><X size={18} /></a></div>
                        {/if}
                    </form>
                </div>
            {/if}
        </nav>

        {#if kalemler.total > 0}
            <div class="table-container">
                <table class="table is-fullwidth">
                    <caption>Bu yerleşke için <b>{kalemler.total}</b> harcama kalem tanımı bulunmaktadır</caption>
                    <thead>
                        <tr>
                            <th>
                                <a class="icon-text" href={sortUrl('title')}>
                                    {#if sort === 'title'}{#if direction === 'asc'}<ArrowUp size={16} />{:else}<ArrowDown size={16} />{/if}{/if}
                                    <span>İsim</span>
                                </a>
                            </th>
                            <th>
                                <a class="icon-text" href={sortUrl('created_at')}>
                                    {#if sort === 'created_at'}{#if direction === 'asc'}<ArrowUp size={16} />{:else}<ArrowDown size={16} />{/if}{/if}
                                    <span>Eklenme Zamanı</span>
                                </a>
                            </th>
                            <th class="has-text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each kalemler.data as kalem}
                            <tr>
                                <td>{kalem.title}</td>
                                <td>{kalem.created_at}</td>
                                <td class="has-text-right">
                                    <a href={`/kalem-form/${bina.id}/${kalem.id}`} class="icon" aria-label="Düzenle"><Pencil size={18} /></a>
                                    <button class="icon button is-white" type="button" aria-label="Sil" onclick={() => deletingId = kalem.id}>
                                        <Trash2 size={18} />
                                    </button>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            {#if kalemler.links?.length > 3}
                <nav class="pagination is-centered" aria-label="Sayfalar">
                    {#each kalemler.links as link}
                        {#if link.url}
                            <a class:is-current={link.active} class="pagination-link" href={link.url} aria-label={link.label}>{@html link.label}</a>
                        {/if}
                    {/each}
                </nav>
            {/if}
        {:else}
            <div class="notification is-warning is-light">
                {search ? 'Aramanızla eşleşen harcama kalemi bulunamadı.' : 'Bu bina için harcama kalem tanımı yapılmamıştır.'}
            </div>
        {/if}
    </main>

    {#if deletingId}
        <div class="modal is-active">
            <button class="modal-background" type="button" aria-label="Kapat" onclick={() => deletingId = null}></button>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Harcama kalemini sil?</p>
                    <button class="delete" type="button" aria-label="Kapat" onclick={() => deletingId = null}></button>
                </header>
                <section class="modal-card-body">Bu işlem geri alınamaz.</section>
                <footer class="modal-card-foot">
                    <form method="POST" action={`/kalem-delete/${bina.id}/${deletingId}`}>
                        <input type="hidden" name="_token" value={csrfToken}>
                        <button class="button is-danger" type="submit">Sil</button>
                    </form>
                    <button class="button" type="button" onclick={() => deletingId = null}>İptal</button>
                </footer>
            </div>
        </div>
    {/if}
</Layout>

<style>
    :global(.pagination-link.is-current) {
        background-color: hsl(217, 71%, 35%);
        border-color: hsl(217, 71%, 35%);
    }
</style>
