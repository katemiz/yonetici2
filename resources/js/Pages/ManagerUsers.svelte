<script>
    import { page } from '@inertiajs/svelte';
    import Layout from './Shared/Layout.svelte';
    import Paginate from './components/Paginate.svelte';

    let { managers } = $props();
    let errors = $derived(page?.props?.errors ?? {});
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
</script>

<svelte:head>
    <title>Yönetici Hesapları - Akıllı Yönetici</title>
</svelte:head>

<Layout>
    <main class="section">
        <div class="container">
            <h1 class="title has-text-weight-light is-size-1">Yönetici Hesapları</h1>
            <h2 class="subtitle has-text-weight-light">Bina yöneticilerini ve bina kotalarını yönetin</h2>

            {#if errors && Object.keys(errors).length}
                <div class="notification is-danger is-light" role="alert">
                    {#each Object.values(errors) as error}
                        <p>{Array.isArray(error) ? error[0] : error}</p>
                    {/each}
                </div>
            {/if}

            <section class="box">
                <h3 class="title is-4">Yeni Yönetici Ekle</h3>
                <form method="POST" action="/admin/managers">
                    <input type="hidden" name="_token" value={csrfToken}>
                    <div class="columns">
                        <div class="column">
                            <label class="label" for="manager-name">Ad</label>
                            <input class="input" id="manager-name" name="name" required maxlength="255">
                        </div>
                        <div class="column">
                            <label class="label" for="manager-lastname">Soyad</label>
                            <input class="input" id="manager-lastname" name="lastname" required maxlength="255">
                        </div>
                        <div class="column">
                            <label class="label" for="manager-email">E-posta</label>
                            <input class="input" id="manager-email" name="email" type="email" required maxlength="255">
                        </div>
                    </div>
                    <div class="columns">
                        <div class="column">
                            <label class="label" for="manager-password">İlk Parola</label>
                            <input class="input" id="manager-password" name="password" type="password" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="column">
                            <label class="label" for="manager-password-confirmation">İlk Parola (Tekrar)</label>
                            <input class="input" id="manager-password-confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="column is-one-quarter">
                            <label class="label" for="manager-quota">Bina Kotası</label>
                            <input class="input" id="manager-quota" name="building_quota" type="number" min="1" value="1" required>
                        </div>
                    </div>
                    <button class="button is-link" type="submit">Yönetici Oluştur</button>
                </form>
            </section>

            {#if managers.data.length}
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Yönetici</th>
                                <th>E-posta</th>
                                <th>Bina Sayısı / Kota</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each managers.data as manager}
                                <tr>
                                    <td colspan="5">
                                        <form class="manager-row" method="POST" action={`/admin/managers/${manager.id}`}>
                                            <input type="hidden" name="_token" value={csrfToken}>
                                            <input type="hidden" name="_method" value="PATCH">
                                            <input class="input" name="name" aria-label="Ad" value={manager.name} required>
                                            <input class="input" name="lastname" aria-label="Soyad" value={manager.lastname} required>
                                            <input class="input" name="email" type="email" aria-label="E-posta" value={manager.email} required>
                                            <span class="quota-count">{manager.building_count} /</span>
                                            <input class="input quota-input" name="building_quota" aria-label="Bina kotası" type="number" min="1" value={manager.building_quota} required>
                                            <span class:has-text-success={manager.is_active} class:has-text-danger={!manager.is_active}>
                                                {manager.is_active ? 'Aktif' : 'Pasif'}
                                            </span>
                                            <button class="button is-link is-small" type="submit">Kaydet</button>
                                        </form>
                                        <form method="POST" action={`/admin/managers/${manager.id}/toggle-active`}>
                                            <input type="hidden" name="_token" value={csrfToken}>
                                            <input type="hidden" name="_method" value="PATCH">
                                            <button class="button is-small {manager.is_active ? 'is-warning' : 'is-success'}" type="submit">
                                                {manager.is_active ? 'Devre Dışı Bırak' : 'Etkinleştir'}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
                <Paginate items={managers} />
            {:else}
                <div class="notification is-light">Henüz yönetici hesabı yok.</div>
            {/if}
        </div>
    </main>
</Layout>

<style>
    .manager-row {
        align-items: center;
        display: grid;
        gap: 0.5rem;
        grid-template-columns: repeat(3, minmax(8rem, 1fr)) auto 6rem 4rem auto;
        margin-bottom: 0.5rem;
    }

    .quota-count {
        white-space: nowrap;
    }

    .quota-input {
        width: 6rem;
    }

    @media (max-width: 768px) {
        .manager-row {
            grid-template-columns: 1fr;
        }
    }
</style>
