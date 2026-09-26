<script>
    import { File, Plus, ReceiptText, BookDown, Search, X } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, records, search = '' } = $props();
    let query = $state('');
    let selectedRecord = $state(null);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    $effect(() => {
        query = search;
    });

    function confirmReceived(id) {
        if (confirm('Bu alacak gelir kaydına dönüştürülecektir. Onaylıyor musunuz?')) {
            document.getElementById(`received-${id}`).submit();
        }
    }
</script>

<svelte:head>
    <title>Alacaklar - {bina?.name ?? 'Akıllı Yönetici'}</title>
</svelte:head>

<Layout>
    <main class="section">
        <div class="container">
            <header class="my-6">
                <h1 class="title has-text-weight-light is-size-1">Alacaklar</h1>
                <h2 class="subtitle has-text-weight-light">{bina?.name}: Alacak Kayıtları</h2>
            </header>

            <div class="level mb-5">
                <div class="level-left">
                    <a href="/kayit-form/alacak" class="button is-link">
                        <Plus size={18} />Alacak Ekle
                    </a>
                </div>
                <div class="level-right">
                    <form method="GET" action="/durum/alacaklar" class="field has-addons">
                        <div class="control has-icons-left">
                            <input class="input" name="search" bind:value={query} placeholder="Ara" aria-label="Alacaklarda ara">
                            <Search size={16} class="icon is-left" />
                        </div>
                        <div class="control">
                            <button class="button" type="submit">Ara</button>
                        </div>
                        {#if search}
                            <div class="control">
                                <a class="button" href="/durum/alacaklar" aria-label="Aramayı temizle"><X size={18} /></a>
                            </div>
                        {/if}
                    </form>
                </div>
            </div>

            {#if records.total > 0}
                <div class="table-container">
                    <table class="table is-fullwidth">
                        <caption>Toplam <b>{records.total}</b> kayıt vardır</caption>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kapı No</th>
                                <th>Borçlu</th>
                                <th>Açıklama</th>
                                <th class="has-text-right">Tutar</th>
                                <th>Dosya</th>
                                <th>Eklenme Zamanı</th>
                                <th class="has-text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each records.data as record}
                                <tr>
                                    <td><a href={`/kayit-gor/${record.id}`}>{record.id}</a></td>
                                    <td>{record.door_no}</td>
                                    <td>{record.resident_name}</td>
                                    <td>{@html record.description ?? ''}</td>
                                    <td class="has-text-right td-tutar">{record.amount} {bina.pbirimi}</td>
                                    <td>
                                        <button class="button is-white p-1" type="button" aria-label="Dosya ekle"
                                            onclick={() => selectedRecord = record.id}>
                                            <Plus size={22} />
                                        </button>
                                        {#each record.files as file}
                                            <a href={`/kayit-dosya-gor/${file.id}`} class="ml-2" title={file.name}>
                                                <File size={22} />
                                            </a>
                                        {/each}
                                    </td>
                                    <td>{record.created_at}</td>
                                    <td class="has-text-right">
                                        <a href={`/makbuzpdf/${record.id}`} class="icon" title="Makbuz">
                                            <ReceiptText size={22} />
                                        </a>
                                            <button class="button is-white p-1" type="button" title="Alındı"
                                                onclick={() => confirmReceived(record.id)}>
                                                <BookDown size={22} />
                                            </button>
                                    </td>












                                    
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                {#if records.links?.length > 3}
                    <nav class="pagination is-centered" aria-label="pagination">
                        {#each records.links as link}
                            {#if link.url}
                                <a class:is-current={link.active} class="pagination-link" href={link.url} aria-label={link.label}>
                                    {@html link.label}
                                </a>
                            {/if}
                        {/each}
                    </nav>
                {/if}
            {:else}
                <div class="notification is-warning is-light">Alacak Kaydı Yoktur</div>
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
                <form method="POST" action={`/kayit-dosya-add/${selectedRecord}/alacaklar`} enctype="multipart/form-data">
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

    .is-inline {
        display: inline;
    }

    :global(.pagination-link.is-current) {
        background-color: hsl(217, 71%, 35%);
        border-color: hsl(217, 71%, 35%);
    }
</style>
