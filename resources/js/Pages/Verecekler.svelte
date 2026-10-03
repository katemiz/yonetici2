<script>
    import { untrack } from 'svelte';
    import { File, Plus, Wallet } from '@lucide/svelte';
    import { router } from '@inertiajs/svelte';
    import Paginate from './components/Paginate.svelte';
    import SearchBox from './components/SearchBox.svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, records, search = '' } = $props();
    let query = $state(untrack(() => search));
    let selectedRecord = $state(null);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let skipInitialSearch = true;

    $effect(() => {
        const currentQuery = query;
        if (skipInitialSearch) {
            skipInitialSearch = false;
            return;
        }

        const timeout = window.setTimeout(() => doSearch(currentQuery), 300);
        return () => window.clearTimeout(timeout);
    });

    function doSearch(query) {
        router.get(
            '/durum/verecekler',
            { search: query },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    }

    function markPaid(id) {
        if (confirm('Bu fatura/borç gider kaydına dönüştürülecektir. Onaylıyor musunuz?')) {
            document.getElementById(`paid-${id}`).submit();
        }
    }
</script>

<svelte:head>
    <title>Ödenecek Fatura ve Borçlar - {bina?.name ?? 'Akıllı Yönetici'}</title>
</svelte:head>

<Layout>
    <main class="section">
        <div class="container">
            <header class="my-6">
                <h1 class="title has-text-weight-light is-size-1">Ödenecek Fatura ve Borçlar</h1>
                <h2 class="subtitle has-text-weight-light">{bina?.name}: Verecek Kayıtları</h2>
            </header>

            <div class="level mb-5">
                <div class="level-left">
                    <a href="/kayit-form/fatura" class="button is-link">
                        <Plus size={18} />Fatura Ekle
                    </a>
                </div>
                <div class="level-right">
                    <div class="field">
                        <SearchBox bind:query={query} placeholder="Ara..." ariaLabel="Ödenecek kayıtlarda ara" />
                    </div>
                </div>
            </div>

            {#if records.total > 0}
                <div class="table-container">
                    <table class="table is-fullwidth">
                        <caption>Toplam <b>{records.total}</b> kayıt vardır</caption>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Açıklama</th>
                                <th class="has-text-right">Tutar</th>
                                <th>&nbsp;</th>
                                <th class="has-text-right">Dosya</th>
                                <th>Son Ödeme</th>
                                <th class="has-text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each records.data as record}
                                <tr>
                                    <td><a href={`/kayit-gor/${record.id}`}>{record.id}</a></td>
                                    <td>{@html record.description ?? ''}</td>
                                    <td class="has-text-right td-tutar">{record.amount} {bina.pbirimi}</td>
                                    <td>
                                        <button class="button is-white p-1" type="button" aria-label="Dosya ekle"
                                            onclick={() => selectedRecord = record.id}>
                                            <Plus size={18} />
                                        </button>
                                    </td>
                                    <td class="has-text-right">
                                        {#each record.files as file}
                                            <a href={file.url ?? `/kayit-dosya-gor/${file.id}`} class="ml-2" title={file.name}>
                                                <File size={18} />
                                            </a>
                                        {/each}
                                    </td>
                                    <td>{record.due_date ?? ''}</td>
                                    <td class="has-text-right">
                                        <form id={`paid-${record.id}`} method="POST" action={`/durum/verecekler/${record.id}/paid`}>
                                            <input type="hidden" name="_token" value={csrfToken}>
                                            <button class="button is-white p-1" type="button" title="Ödendi"
                                                aria-label="Ödendi" onclick={() => markPaid(record.id)}>
                                                <Wallet size={18} />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Paginate items={records} query={query} />
            {:else}
                <div class="notification is-warning is-light">Ödenecek Fatura ve Borç Kaydı Yoktur</div>
            {/if}
        </div>
    </main>

    {#if selectedRecord}
        <div class="modal is-active">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Kayıtlara Dosya Ekleme</p>
                    <button class="delete" aria-label="close" onclick={() => selectedRecord = null}></button>
                </header>
                <form method="POST" action={`/kayit-dosya-add/${selectedRecord}/verecekler`} enctype="multipart/form-data">
                    <input type="hidden" name="_token" value={csrfToken}>
                    <section class="modal-card-body">
                        <input class="file-input" type="file" name="dosyalar[]" multiple required>
                    </section>
                    <footer class="modal-card-foot">
                        <button class="button is-success" type="submit">Yükle</button>
                        <button class="button" type="button" onclick={() => selectedRecord = null}>İptal</button>
                    </footer>
                </form>
            </div>
        </div>
    {/if}
</Layout>

<style>
    .td-tutar {
        white-space: nowrap;
        width: 150px;
        vertical-align: top;
    }

    :global(.pagination-link.is-current) {
        background-color: hsl(217, 71%, 35%);
        border-color: hsl(217, 71%, 35%);
    }
</style>
