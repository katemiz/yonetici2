<script>
    import { ArrowLeft, Pencil, Plus } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, bedeller } = $props();
</script>

<svelte:head>
    <title>Hizmet Bedelleri - {bina.name}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <header class="my-6">
            <h1 class="title has-text-weight-light is-size-1">Bina Hizmet Bedelleri</h1>
            <h2 class="subtitle has-text-weight-light">Sabit ve/veya sayaçlı hizmet/ürünlere ait bedeller</h2>
        </header>

        <a href={`/bina-view/${bina.id}`} class="icon-text">
            <span class="icon"><ArrowLeft size={18} /></span><span>Geri</span>
        </a>

        <nav class="level my-6">
            <div class="level-left">
                <a href={`/bedel-form/${bina.id}`} class="button is-link">
                    <span class="icon is-small"><Plus size={18} /></span>
                    <span>Ürün/Hizmet Bedeli Ekle</span>
                </a>
            </div>
        </nav>

        {#if bedeller.total > 0}
            <div class="table-container">
                <table class="table is-fullwidth">
                    <caption>Toplam <b>{bedeller.total}</b> ürün/hizmet bedeli vardır</caption>
                    <thead>
                        <tr>
                            <th>Hizmet/Ürün Tanımı</th>
                            <th>Hizmet/Ürün Türü</th>
                            <th>Birimi</th>
                            <th>Bedel</th>
                            <th>Eklenme Zamanı</th>
                            <th class="has-text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each bedeller.data as bedel}
                            <tr>
                                <td>{bedel.title}</td>
                                <td>{bedel.type}</td>
                                <td>{bedel.unit}</td>
                                <td>{bedel.amount} {bina.pbirimi}</td>
                                <td>{bedel.created_at}</td>
                                <td class="has-text-right">
                                    <a href={`/bedel-form/${bina.id}/${bedel.id}`} class="icon" aria-label="Düzenle">
                                        <Pencil size={18} />
                                    </a>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            {#if bedeller.links?.length > 3}
                <nav class="pagination is-centered" aria-label="Sayfalar">
                    {#each bedeller.links as link}
                        {#if link.url}
                            <a class:is-current={link.active} class="pagination-link" href={link.url} aria-label={link.label}>{@html link.label}</a>
                        {/if}
                    {/each}
                </nav>
            {/if}
        {:else}
            <div class="notification is-warning is-light">Bina için tanımlanmış ürün/hizmet bedeli bulunmamaktadır.</div>
        {/if}
    </main>
</Layout>

<style>
    :global(.pagination-link.is-current) {
        background-color: hsl(217, 71%, 35%);
        border-color: hsl(217, 71%, 35%);
    }
</style>
