<script>
    import { untrack } from 'svelte';
    import { ArrowLeft, Building2 } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina = null, paralar, oldInput = {}, errors = {} } = $props();
    let city = $state(untrack(() => String(oldInput.binacity || bina?.city || '6')));
    let currency = $state(untrack(() => oldInput.parabirimi || bina?.pbirimi || 'belirsiz'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
</script>

<svelte:head>
    <title>{bina ? 'Bina Güncelle' : 'Yeni Bina Ekle'} - Akıllı Yönetici</title>
</svelte:head>

<Layout>
    <main class="section container">
        <header class="my-6">
            <h1 class="title has-text-weight-light is-size-1">{bina ? 'Bina Güncelle' : 'Yeni Bina Ekle'}</h1>
        </header>

        <a href="/bina-list" class="icon-text">
            <span class="icon"><ArrowLeft size={18} /></span>
            <span>Binalarıma dön</span>
        </a>

        <div class="column box mt-6">
            <form action={bina ? `/bina-update/${bina.id}` : '/bina-add'} method="POST">
                <input type="hidden" name="_token" value={csrfToken}>

                {#if errors.quota}
                    <div class="notification is-warning is-light" role="alert">{errors.quota}</div>
                {/if}

                <div class="field">
                    <label class="label" for="currency">Kullanılacak Para Birimi</label>
                    <div class="control">
                        <div class="select">
                            <select id="currency" name="parabirimi" bind:value={currency}>
                                <option value="belirsiz">Para Birimi Seçiniz</option>
                                {#each Object.entries(paralar) as [key, label]}
                                    <option value={key}>{label}</option>
                                {/each}
                            </select>
                        </div>
                    </div>
                    {#if errors.parabirimi}
                        <p class="help has-text-danger">{errors.parabirimi[0]}</p>
                    {/if}
                </div>

                <div class="field">
                    <label class="label" for="building-name">Bina İsmi</label>
                    <div class="control">
                        <input
                            class="input"
                            id="building-name"
                            type="text"
                            name="binaname"
                            placeholder="Pembeköşk Apartmanı"
                            value={oldInput.binaname ?? bina?.name ?? ''}
                        >
                    </div>
                    {#if errors.binaname}
                        <p class="help has-text-danger">{errors.binaname[0]}</p>
                    {/if}
                </div>

                <div class="field">
                    <label class="label" for="building-address">Adres</label>
                    <div class="control">
                        <input
                            class="input"
                            id="building-address"
                            type="text"
                            name="binaaddress"
                            placeholder="Yalıkavak Sok"
                            value={oldInput.binaaddress ?? bina?.address ?? ''}
                        >
                    </div>
                    {#if errors.binaaddress}
                        <p class="help has-text-danger">{errors.binaaddress[0]}</p>
                    {/if}
                </div>

                <div class="field">
                    <label class="label" for="city">Şehir</label>
                    <div class="control">
                        <div class="select">
                            <select id="city" name="binacity" bind:value={city}>
                                <option value="6">Ankara</option>
                                <option value="34">İstanbul</option>
                            </select>
                        </div>
                    </div>
                    {#if errors.binacity}
                        <p class="help has-text-danger">{errors.binacity[0]}</p>
                    {/if}
                </div>

                <div class="field">
                    <label class="label" for="resident-access-code">Bina sakini giriş kodu</label>
                    {#if bina?.resident_access_configured}
                        <div class="notification is-success is-light py-2">
                            Mevcut ortak giriş kodu yapılandırılmıştır.
                        </div>
                    {/if}
                    <div class="control">
                        <input
                            class="input"
                            id="resident-access-code"
                            type="text"
                            name="resident_access_code"
                            value={oldInput.resident_access_code ?? ''}
                            placeholder="Sakinlerin kullanacağı ortak kod"
                            autocomplete="off"
                        >
                    </div>
                    {#if errors.resident_access_code}
                        <p class="help has-text-danger">{errors.resident_access_code[0]}</p>
                    {/if}
                    <p class="help">
                        Sakinler telefon numarası ve bu ortak kod ile yalnızca görüntüleme alanına giriş yapar.
                        Güvenlik nedeniyle kayıtlı kod gösterilmez. Yeni kod girmek için bu alanı doldurun;
                        güncellemede boş bırakırsanız mevcut kod korunur.
                    </p>
                </div>

                <div class="field is-grouped is-grouped-right">
                    <div class="control">
                        <button type="submit" class="button is-link is-light">
                            {bina ? 'Güncelle' : 'Kaydet'}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</Layout>
