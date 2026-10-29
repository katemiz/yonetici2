<script>
    import { router } from "@inertiajs/svelte";
    import { ArrowLeft, UserCog } from "@lucide/svelte";
    import Layout from "./Shared/Layout.svelte";

    let { bina, sakinler, current_manager } = $props();

    function chooseManager(sakin) {
        router.post(`/admin/managers/assign/${bina.id}`, {
            name: sakin.name,
            lastname: sakin.lastname,
            email: sakin.email,
            building_quota: bina.building_quota ?? 1,
        });
    }
</script>

<svelte:head>
    <title>Yönetici Seç - {bina.name} - Akıllı Yönetici</title>
</svelte:head>

<Layout>
    <main class="section container">
        <header class="my-6">
            <h1 class="title has-text-weight-light is-size-1">Yönetici Seç</h1>
            <h2 class="subtitle has-text-weight-light">
                {bina.name} için yeni yönetici seçin
            </h2>
        </header>

        <a href={`/bina-view/${bina.id}`} class="icon-text">
            <span class="icon"><ArrowLeft size={18} /></span>
            <span>Geri</span>
        </a>

        {#if current_manager}
            <div class="notification is-info is-light" role="status">
                <p>
                    <strong>Şu anki yönetici:</strong>
                    {current_manager.name}
                    {current_manager.id}
                    {current_manager.lastname}
                    ({current_manager.email})
                    {#if current_manager.is_active}Aktif{:else}Pasif{/if}
                </p>
            </div>
        {/if}

        {#if sakinler.length > 0}
            <div class="table-container">
                <table class="table is-fullwidth">
                    <caption
                        >Bu yerleşkede yönetici olarak seçilecek sakinler
                        listelenmektedir</caption
                    >
                    <thead>
                        <tr>
                            <th>İsim</th>
                            <th>E-posta</th>
                            <th class="has-text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each sakinler as sakin}
                            <tr>
                                <td
                                    >{sakin.name}
                                    {sakin.id}
                                    {sakin.lastname}

                                    {#if sakin.id === current_manager?.id}
                                        <br />
                                        <span class="tag is-info is-light"
                                            >Şu anki yönetici</span
                                        >
                                    {/if}
                                </td>
                                <td>{sakin.email}</td>
                                <td class="has-text-right">
                                    <button
                                        class="button is-link"
                                        type="button"
                                        onclick={() => chooseManager(sakin)}
                                    >
                                        <span class="icon is-small"
                                            ><UserCog size={18} /></span
                                        >
                                        <span>Yönetici Olarak Ata</span>
                                    </button>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {:else}
            <div class="notification is-warning is-light">
                Bu bina/yerleşke için oturan tanımı yapılmamıştır.
            </div>
        {/if}
    </main>
</Layout>
