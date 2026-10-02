<script>
    import { untrack } from 'svelte';
    import { ArrowLeft } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, bedel = null, tur_secenek, units, oldInput = {}, errors = {} } = $props();
    let type = $state(untrack(() => oldInput.tur ?? bedel?.tur ?? 'belirsiz'));
    let unit = $state(untrack(() => oldInput.birim ?? bedel?.unit ?? 'belirsiz'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const pageTitle = $derived(bedel ? 'Bedel Tanımı Güncelle' : 'Bedel Tanımı');

    function value(field, fallback = '') {
        return oldInput?.[field] ?? fallback ?? '';
    }

    function firstError(field) {
        const error = errors?.[field];
        return Array.isArray(error) ? error[0] : error;
    }
</script>

<svelte:head>
    <title>{pageTitle} - {bina.name}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">{pageTitle}</h1>
        <h2 class="subtitle">{bina.name} için bedel tanımlaması</h2>

        <div class="mb-5">
            <a href={`/bina-view/${bina.id}`} class="icon-text">
                <span class="icon"><ArrowLeft size={18} /></span>
                <span>Geri</span>
            </a>
        </div>

        <form action={bedel ? `/bedel-upd/${bina.id}/${bedel.id}` : `/bedel-add/${bina.id}`} method="POST">
            <input type="hidden" name="_token" value={csrfToken}>

            <div class="box">
                <div class="field">
                    <label class="label" for="service-title">Tanım</label>
                    <div class="control">
                        <input
                            class="input"
                            id="service-title"
                            name="title"
                            type="text"
                            placeholder="Sıcak Su Bedeli veya Hizmetli Ücreti"
                            value={value('title', bedel?.title)}
                        >
                    </div>
                    {#if firstError('title')}<p class="help has-text-danger">{firstError('title')}</p>{/if}
                </div>

                <div class="columns">
                    <div class="column field">
                        <label class="label" for="service-type">Bedel Türü</label>
                        <div class="control">
                            <div class="select">
                                <select id="service-type" name="tur" bind:value={type}>
                                    <option value="belirsiz">Tür Seçiniz</option>
                                    {#each Object.entries(tur_secenek) as [key, label]}
                                        <option value={key}>{label}</option>
                                    {/each}
                                </select>
                            </div>
                        </div>
                        {#if firstError('tur')}<p class="help has-text-danger">{firstError('tur')}</p>{/if}
                    </div>

                    <div class="column field">
                        <label class="label" for="service-amount">Birim Bedel/Ücret</label>
                        <div class="control">
                            <input
                                class="input"
                                id="service-amount"
                                name="bedel"
                                type="number"
                                placeholder="25"
                                value={value('bedel', bedel?.bedel)}
                            >
                        </div>
                        {#if firstError('bedel')}<p class="help has-text-danger">{firstError('bedel')}</p>{/if}
                    </div>

                    <div class="column field">
                        <label class="label" for="service-unit">Birim</label>
                        <div class="control">
                            <div class="select">
                                <select id="service-unit" name="birim" bind:value={unit}>
                                    <option value="belirsiz">Birim Seçiniz</option>
                                    {#each Object.entries(units) as [key, label]}
                                        <option value={key}>{@html label}</option>
                                    {/each}
                                </select>
                            </div>
                        </div>
                        {#if firstError('birim')}<p class="help has-text-danger">{firstError('birim')}</p>{/if}
                    </div>
                </div>

                <div class="buttons is-right">
                    <button class="button is-link" type="submit">{bedel ? 'Güncelle' : 'Kaydet'}</button>
                </div>
            </div>
        </form>
    </main>
</Layout>
