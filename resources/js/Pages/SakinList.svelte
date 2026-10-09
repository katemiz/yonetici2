<script>
    import { untrack } from "svelte";
    import { router } from "@inertiajs/svelte";
    import {
        ArrowDown,
        ArrowLeft,
        ArrowUp,
        Pencil,
        Plus,
    } from "@lucide/svelte";
    import Layout from "./Shared/Layout.svelte";
    import SearchBox from "./components/SearchBox.svelte";

    let {
        bina,
        sakinler,
        search = "",
        sort = "created_at",
        direction = "desc",
        status = "1",
    } = $props();
    let query = $state(untrack(() => search));
    let skipInitialSearch = true;

    function doSearch() {
        router.get(
            `/sakin-list/${bina.id}`,
            { search: query, sort, direction, status },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    }

    $effect(() => {
        const currentQuery = query;
        if (skipInitialSearch) {
            skipInitialSearch = false;
            return;
        }

        if (!currentQuery || currentQuery.length > 2) doSearch();
    });

    function sortUrl(field) {
        const params = new URLSearchParams();
        if (query) params.set("search", query);
        params.set("status", status);
        params.set("sort", field);
        params.set(
            "direction",
            sort === field && direction === "asc" ? "desc" : "asc",
        );
        return `/sakin-list/${bina.id}?${params.toString()}`;
    }
</script>

<svelte:head>
    <title>Bina Sakinleri - {bina.name}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <header class="my-6">
            <h1 class="title has-text-weight-light is-size-1">
                Bina Sakinleri
            </h1>
            <h2 class="subtitle has-text-weight-light">
                {bina.name} oturan sakinler
            </h2>
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
            <div class="level-right">
                <form
                    method="GET"
                    action={`/sakin-list/${bina.id}`}
                    class="field is-grouped mr-5"
                    aria-label="Sakin durumunu filtrele"
                >
                    {#if query}<input
                            type="hidden"
                            name="search"
                            value={query}
                        />{/if}
                    <input type="hidden" name="sort" value={sort} />
                    <input type="hidden" name="direction" value={direction} />
                    <div class="control">
                        <label class="radio">
                            <input
                                type="radio"
                                name="status"
                                value="1"
                                checked={status === "1"}
                                onchange={(event) =>
                                    event.currentTarget.form.requestSubmit()}
                            />
                            Güncel
                        </label>
                    </div>
                    <div class="control">
                        <label class="radio">
                            <input
                                type="radio"
                                name="status"
                                value="0"
                                checked={status === "0"}
                                onchange={(event) =>
                                    event.currentTarget.form.requestSubmit()}
                            />
                            Eski
                        </label>
                    </div>
                </form>
                <div class="field">
                    <SearchBox
                        bind:query
                        placeholder="Ara..."
                        ariaLabel="Sakinlerde ara"
                    />
                </div>
            </div>
        </nav>

        {#if sakinler.total > 0}
            <div class="table-container">
                <table class="table is-fullwidth">
                    <caption
                        >Bu yerleşkede <b>{sakinler.total}</b>
                        {status === "1" ? "güncel" : "eski"} sakin bulunmaktadır</caption
                    >
                    <thead>
                        <tr>
                            <th>
                                <a class="icon-text" href={sortUrl("name")}>
                                    {#if sort === "name"}
                                        {#if direction === "asc"}<ArrowUp
                                                size={16}
                                            />{:else}<ArrowDown
                                                size={16}
                                            />{/if}
                                    {/if}
                                    <span>İsim</span>
                                </a>
                            </th>
                            <th>
                                <a
                                    class="icon-text"
                                    href={sortUrl("created_at")}
                                >
                                    {#if sort === "created_at"}
                                        {#if direction === "asc"}<ArrowUp
                                                size={16}
                                            />{:else}<ArrowDown
                                                size={16}
                                            />{/if}
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
                                <td
                                    ><a
                                        href={`/sakin-view/${bina.id}/${sakin.id}`}
                                        >{sakin.name} {sakin.lastname}</a
                                    ></td
                                >
                                <td>{sakin.created_at}</td>
                                <td class="has-text-right">
                                    <a
                                        href={`/sakin-form/${bina.id}/${sakin.id}`}
                                        class="icon"
                                        aria-label="Düzenle"
                                    >
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
                            <a
                                class:is-current={link.active}
                                class="pagination-link"
                                href={link.url}
                                aria-label={link.label}
                            >
                                {@html link.label}
                            </a>
                        {/if}
                    {/each}
                </nav>
            {/if}
        {:else}
            <div class="notification is-warning is-light">
                {search
                    ? "Aramanızla eşleşen sakin bulunamadı."
                    : status === "1"
                      ? "Bu bina/yerleşke için oturan tanımı yapılmamıştır."
                      : "Bu bina/yerleşke için eski sakin kaydı bulunmamaktadır."}
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
