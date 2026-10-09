<script>
    import { untrack } from "svelte";
    import { File, Plus, TagPlus, ReceiptText, BookDown } from "@lucide/svelte";
    import { page, router } from "@inertiajs/svelte";
    import Paginate from "./components/Paginate.svelte";
    import SearchBox from "./components/SearchBox.svelte";
    import Layout from "./Shared/Layout.svelte";

    let { bina, records, search = "" } = $props();
    let isResident = $derived(page?.props?.userType === "resident");
    let query = $state(untrack(() => search));
    let selectedRecord = $state(null);
    let listUrl = $derived(
        isResident ? "/resident-status/alacaklar" : "/durum/alacaklar",
    );

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content ?? "";
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
            listUrl,
            { search: query },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    }

    function confirmReceived(id) {
        if (
            confirm(
                "Bu alacak gelir kaydına dönüştürülecektir. Onaylıyor musunuz?",
            )
        ) {
            document.getElementById(`received-${id}`).submit();
        }
    }
</script>

<svelte:head>
    <title>Alacaklar - {bina?.name ?? "Akıllı Yönetici"}</title>
</svelte:head>

<Layout>
    <main class="section">
        <div class="container">
            <header class="my-6">
                <h1 class="title has-text-weight-light is-size-1">Alacaklar</h1>
                <h2 class="subtitle has-text-weight-light">
                    {bina?.name}: Alacak Kayıtları
                </h2>
            </header>

            {#if isResident}
                <div class="notification is-info is-light">
                    Bu alan yalnızca görüntüleme içindir.
                </div>
            {/if}

            <div class="level mb-5">
                <div class="level-left">
                    {#if !isResident}
                        <a href="/kayit-form/alacak" class="button is-link">
                            <Plus size={18} />Alacak Ekle
                        </a>
                    {/if}
                </div>
                <div class="level-right">
                    <div class="field">
                        <SearchBox
                            bind:query
                            placeholder="Ara..."
                            ariaLabel="Alacaklarda ara"
                        />
                    </div>
                </div>
            </div>

            {#if records.total > 0}
                <div class="table-container">
                    <table class="table is-fullwidth">
                        <caption
                            >Toplam <b>{records.total}</b> kayıt vardır</caption
                        >
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kapı No</th>
                                <th>Borçlu</th>
                                <th>Açıklama</th>
                                <th class="has-text-right">Tutar</th>
                                {#if !isResident}<th>Dosya</th>{/if}
                                <th>Eklenme Zamanı</th>
                                <th class="has-text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each records.data as record}
                                <tr>
                                    <td
                                        >{#if isResident}{record.id}{:else}<a
                                                href={`/kayit-gor/${record.id}`}
                                                >{record.id}</a
                                            >{/if}</td
                                    >
                                    <td>{record.door_no}</td>
                                    <td>{record.resident_name}</td>
                                    <td
                                        >{#if isResident}{record.description ??
                                                ""}{:else}{@html record.description ??
                                                ""}{/if}</td
                                    >
                                    <td class="has-text-right td-tutar"
                                        >{record.amount} {bina.pbirimi}</td
                                    >
                                    {#if !isResident}
                                        <td>
                                            <button
                                                class="button is-white p-1"
                                                type="button"
                                                aria-label="Dosya ekle"
                                                onclick={() =>
                                                    (selectedRecord =
                                                        record.id)}
                                            >
                                                <TagPlus size={22} />
                                            </button>
                                            {#each record.files as file}
                                                <a
                                                    href={file.url ??
                                                        `/kayit-dosya-gor/${file.id}`}
                                                    class="ml-2"
                                                    title={file.name}
                                                >
                                                    <File size={22} />
                                                </a>
                                            {/each}
                                        </td>
                                    {/if}
                                    <td>{record.created_at}</td>
                                    <td class="has-text-right">
                                        {#if isResident}
                                            {#if record.can_view_receipt}
                                                <a
                                                    href={`/makbuz/${record.id}`}
                                                    class="icon"
                                                    title="Makbuz"
                                                    aria-label="Makbuz"
                                                >
                                                    <ReceiptText size={22} />
                                                </a>
                                            {/if}
                                        {:else}
                                            <a
                                                href={`/makbuzpdf/${record.id}`}
                                                class="icon"
                                                title="Makbuz"
                                            >
                                                <ReceiptText size={22} />
                                            </a>
                                            <button
                                                class="button is-white p-1"
                                                type="button"
                                                title="Alındı"
                                                onclick={() =>
                                                    confirmReceived(record.id)}
                                            >
                                                <BookDown size={22} />
                                            </button>
                                        {/if}
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Paginate items={records} {query} />
            {:else}
                <div class="notification is-warning is-light">
                    Alacak Kaydı Yoktur
                </div>
            {/if}
        </div>
    </main>

    {#if selectedRecord}
        <div class="modal is-active">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Kayıtlara Dosya Ekleme</p>
                    <button
                        class="delete"
                        aria-label="close"
                        onclick={() => (selectedRecord = null)}
                    ></button>
                </header>
                <form
                    method="POST"
                    action={`/kayit-dosya-add/${selectedRecord}/alacaklar`}
                    enctype="multipart/form-data"
                >
                    <input type="hidden" name="_token" value={csrfToken} />
                    <section class="modal-card-body">
                        <input
                            class="file-input"
                            type="file"
                            name="dosyalar[]"
                            multiple
                            required
                        />
                    </section>
                    <footer class="modal-card-foot">
                        <button class="button is-success" type="submit"
                            >Yükle</button
                        >
                        <button
                            class="button"
                            type="button"
                            onclick={() => (selectedRecord = null)}
                            >İptal</button
                        >
                    </footer>
                </form>
            </div>
        </div>
    {/if}
</Layout>
