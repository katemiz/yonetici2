<script>
    import { untrack } from 'svelte';
    import { File, Plus, Receipt, Paperclip } from '@lucide/svelte';
    import { page, router } from '@inertiajs/svelte';
    import Paginate from './components/Paginate.svelte';
    import Title from './components/Title.svelte';
    import SearchBox from './components/SearchBox.svelte';


    import Layout from './Shared/Layout.svelte';

    let { bina, records, search = '' } = $props();
    let isResident = $derived(page?.props?.userType === 'resident');
    let query = $state(untrack(() => search));
    let selectedRecord = $state(null);
    let listUrl = $derived(isResident ? '/resident-status/gelirler' : '/durum/gelirler');
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
            "/durum/gelirler",
            {
                search: query,
            },
            {
                preserveState: true,
                replace: true, // Prevents flooding browser history with every single keystroke
                preserveScroll: true,
            },
        );
    }











</script>

<svelte:head>
    <title>Gelirler - {bina?.name ?? 'Akıllı Yönetici'}</title>
</svelte:head>

<Layout>

    <div class="section container">

            <Title title="Gelirler" subtitle="Ayrıntılı Gelir Kayıtları"/>

            {#if isResident}
                <div class="notification is-info is-light">Bu alan yalnızca görüntüleme içindir.</div>
            {/if}

            <div class="level mb-5">
                <div class="level-left">
                    {#if !isResident}
                        <a href="/kayit-form/gelir" class="button is-link">
                            <Plus size={18} />Gelir Ekle
                        </a>
                    {/if}
                </div>
                <div class="level-right">

                        <SearchBox bind:query={query} placeholder="Ara..." />



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
                                <th>Kaynak</th>
                                <th>Açıklama</th>
                                <th class="has-text-right">Tutar</th>
                                {#if !isResident}
                                    <th>&nbsp;</th>
                                {/if}
                                <th class="has-text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each records.data as record}
                                <tr>
                                    <td>{#if isResident}{record.id}{:else}<a href={`/kayit-gor/${record.id}`}>{record.id}</a>{/if}</td>
                                    <td>{record.door_no}</td>
                                    <td>{record.resident_name}</td>
                                    <td>{#if isResident}{record.description ?? ''}{:else}{@html record.description ?? ''}{/if}</td>
                                    <td class="has-text-right td-tutar">{record.amount} {bina.pbirimi}</td>
                                    {#if !isResident}
                                        <td>
                                            <button class="button is-white p-1" type="button" aria-label="Dosya ekle"
                                                onclick={() => selectedRecord = record.id}>
                                                <Paperclip size={18} />
                                            </button>

                                            {#each record.files as file}
                                                <a href={`/kayit-dosya-gor/${file.id}`} class="ml-2" title={file.name}>
                                                    <File size={18} />
                                                </a>
                                            {/each}
                                        </td>

                                    {/if}
                                    <td class="has-text-right">
                                        {#if isResident}
                                            {#if record.can_view_receipt}
                                                <a href={`/makbuz/${record.id}`} class="icon" title="Makbuz" aria-label="Makbuz">
                                                    <Receipt size={18} />
                                                </a>
                                            {/if}
                                        {:else}
                                            <a href={`/makbuzpdf/${record.id}`} class="icon" title="Makbuz">
                                                <Receipt size={18} />
                                            </a>
                                        {/if}
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Paginate items={records} query={query} />
            {:else}
                <div class="notification is-warning is-light">Gelir kaydı yoktur</div>
            {/if}
    </div>

    {#if selectedRecord}
        <div class="modal is-active">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Kayıtlara Dosya Ekleme</p>
                    <button class="delete" aria-label="close" onclick={() => selectedRecord = null}></button>
                </header>
                <form method="POST" action={`/kayit-dosya-add/${selectedRecord}/gelirler`} enctype="multipart/form-data">
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
