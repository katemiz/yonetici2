<script>
    import { untrack } from 'svelte';
    import { Eye, Gauge, Pencil, Plus, Settings } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, meters, activeMeterId: initialMeterId, oldInput = {}, errors = {} } = $props();
    const restoredReading = untrack(() => meters
        .flatMap((meter) => meter.residents.flatMap((resident) =>
            resident.readings.map((reading) => ({ reading, meter, resident }))
        ))
        .find((item) => Number(item.reading.id) === Number(oldInput.reading_id)));
    let activeMeterId = $state(untrack(() => Number(oldInput.bedel_id ?? initialMeterId ?? 0)));
    let selectedReading = $state(null);
    let editingReading = $state(restoredReading?.reading ?? null);
    let readingForm = $state(untrack(() => ({
        bedel_id: oldInput.bedel_id ?? initialMeterId ?? '',
        sakin_id: oldInput.sakin_id ?? '',
        okuma_degeri: oldInput.okuma_degeri ?? '',
        okuma_tarihi: oldInput.okuma_tarihi ?? '',
        note: oldInput.note ?? ''
    })));
    let readingModal = $state(untrack(() => Object.keys(errors ?? {}).length > 0));
    let chargeModal = $state(false);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const activeMeter = $derived(meters.find((meter) => Number(meter.id) === Number(activeMeterId)));
    const formAction = $derived(editingReading ? `/sayac-okuma/${editingReading.id}` : '/sayac-okuma');
    const chargeMeter = $derived(
        selectedReading
            ? meters.find((meter) => Number(meter.id) === Number(selectedReading.meterId))
            : null
    );
    const chargeResident = $derived(
        selectedReading
            ? chargeMeter?.residents.find((resident) => Number(resident.id) === Number(selectedReading.residentId))
            : null
    );
    const chargeReading = $derived(
        selectedReading
            ? chargeResident?.readings.find((reading) => Number(reading.id) === Number(selectedReading.readingId))
            : null
    );
    const chargePreviousReading = $derived.by(() => {
        if (!chargeResident || !chargeReading) return null;
        const index = chargeResident.readings.findIndex((reading) => reading.id === chargeReading.id);
        return chargeResident.readings[index + 1] ?? null;
    });
    const chargeAmount = $derived(
        chargeReading && chargeMeter
            ? Math.abs(chargeReading.value - (chargePreviousReading?.value ?? 0)) * chargeMeter.rate
            : 0
    );

    function firstError(field) {
        const error = errors?.[field];
        return Array.isArray(error) ? error[0] : error;
    }

    function openReading(meter, resident, reading = null) {
        editingReading = reading;
        readingForm = {
            bedel_id: meter.id,
            sakin_id: resident.id,
            okuma_degeri: reading?.value ?? '',
            okuma_tarihi: reading?.date ?? '',
            note: reading?.note ?? ''
        };
        readingModal = true;
    }

    function openCharge(meter, resident, reading) {
        selectedReading = {
            meterId: meter.id,
            residentId: resident.id,
            readingId: reading.id
        };
        chargeModal = true;
    }

    function closeReading() {
        readingModal = false;
        editingReading = null;
    }

    function closeCharge() {
        chargeModal = false;
        selectedReading = null;
    }

    function formatAmount(value) {
        return new Intl.NumberFormat('tr-TR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }
</script>

<svelte:head>
    <title>Sayaç Okumaları - {bina?.name ?? 'Akıllı Yönetici'}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <header class="my-6">
            <h1 class="title has-text-weight-light is-size-1">Sayaç Okumaları</h1>
            <h2 class="subtitle has-text-weight-light">Sayaç Okumalarına Tabi Olan Veriler</h2>
        </header>

        {#if Object.keys(errors ?? {}).length > 0}
            <div class="notification is-danger is-light" role="alert">
                Okuma kaydedilemedi. Lütfen işaretli alanları kontrol ediniz.
            </div>
        {/if}

        {#if meters.length === 0}
            <div class="notification is-light">Bu bina için tanımlı sayaç bulunmamaktadır.</div>
        {:else}
            <div class="tabs is-boxed">
                <ul>
                    {#each meters as meter}
                        <li class={Number(activeMeterId) === Number(meter.id) ? 'is-active' : ''}>
                            <a href={`#meter-${meter.id}`} onclick={(event) => { event.preventDefault(); activeMeterId = meter.id; }}>
                                <span class="icon is-small"><Settings size={16} /></span>
                                <span>{meter.title}</span>
                            </a>
                        </li>
                    {/each}
                </ul>
            </div>

            {#if activeMeter}
                {#each activeMeter.residents as resident}
                    <section class="box mb-4 has-background-light">
                        <div class="columns is-vcentered mb-0">
                            <div class="column">
                                <h3 class="title is-4">{resident.door_no} - {resident.name} {resident.lastname}</h3>
                            </div>
                            <div class="column has-text-right has-text-left-mobile">
                                <button class="button is-link" type="button" onclick={() => openReading(activeMeter, resident)}>
                                    <span class="icon"><Plus size={18} /></span>
                                    <span>Sayaç Okuma Ekle</span>
                                </button>
                            </div>
                        </div>

                        {#if resident.readings.length > 0}
                            <div class="table-container">
                                <table class="table is-fullwidth is-striped is-hoverable mt-4">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Okuma<br>Tarihi</th>
                                            <th class="has-text-right">Okuma<br>Değeri</th>
                                            <th>Fatura<br>Durumu</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each resident.readings as reading, index}
                                            <tr>
                                                <td>{resident.readings.length - index}. {activeMeter.title}</td>
                                                <td>{reading.formatted_date}</td>
                                                <td class="has-text-right is-size-6 has-text-weight-bold">{reading.value}</td>
                                                <td>
                                                    {#if reading.record_id > 0}
                                                        <a href={`/kayit-gor/${reading.record_id}`}>{reading.status}</a>
                                                    {:else}
                                                        {reading.status}
                                                    {/if}
                                                </td>
                                                <td>
                                                    {#if reading.status === 'OKUNDU' && reading.record_id < 1}
                                                        <div class="buttons">
                                                            <button class="button" type="button" aria-label="Okumayı düzenle" onclick={() => openReading(activeMeter, resident, reading)}>
                                                                <span class="icon is-small"><Pencil size={16} /></span>
                                                            </button>
                                                            <button class="button" type="button" onclick={() => openCharge(activeMeter, resident, reading)}>
                                                                <span class="icon"><Gauge size={16} /></span>
                                                                <span>Bedel</span>
                                                            </button>
                                                        </div>
                                                    {:else}
                                                        <a href={`/kayit-gor/${reading.record_id}`} class="button" aria-label="Kaydı görüntüle">
                                                            <span class="icon is-small"><Eye size={16} /></span>
                                                        </a>
                                                    {/if}
                                                </td>
                                            </tr>
                                            {#if reading.note}
                                                <tr>
                                                    <td colspan="5" class="is-size-7 has-text-grey">{reading.note}</td>
                                                </tr>
                                            {/if}
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        {/if}
                    </section>
                {/each}
            {/if}
        {/if}
    </main>

    <div class:is-active={readingModal} class="modal">
        <button class="modal-background" type="button" aria-label="Kapat" onclick={closeReading}></button>
        <div class="modal-card">
            <form action={formAction} method="POST">
                <input type="hidden" name="_token" value={csrfToken}>
                <input type="hidden" name="bedel_id" value={readingForm.bedel_id}>
                <input type="hidden" name="sakin_id" value={readingForm.sakin_id}>
                {#if editingReading}
                    <input type="hidden" name="reading_id" value={editingReading.id}>
                {/if}
                <header class="modal-card-head">
                    <p class="modal-card-title">
                        {editingReading ? 'Sayaç Okumasını Düzenle' : 'Yeni Sayaç Okuma'}
                    </p>
                    <button class="delete" type="button" aria-label="Kapat" onclick={closeReading}></button>
                </header>
                <section class="modal-card-body">
                    <div class="field">
                        <label class="label" for="reading-value">Okunan Değer</label>
                        <div class="control">
                            <input class="input" id="reading-value" name="okuma_degeri" type="number" min="0" step="any" placeholder="Okuma Değeri" bind:value={readingForm.okuma_degeri} required>
                        </div>
                        {#if firstError('okuma_degeri')}
                            <p class="help is-danger">{firstError('okuma_degeri')}</p>
                        {/if}
                    </div>
                    <div class="field">
                        <label class="label" for="reading-date">Okuma Tarihi</label>
                        <div class="control">
                            <input class="input" id="reading-date" name="okuma_tarihi" type="date" bind:value={readingForm.okuma_tarihi} required>
                        </div>
                        {#if firstError('okuma_tarihi')}
                            <p class="help is-danger">{firstError('okuma_tarihi')}</p>
                        {/if}
                    </div>
                    <div class="field">
                        <label class="label" for="reading-note">Ek Bilgi (Varsa)</label>
                        <div class="control">
                            <textarea class="textarea" id="reading-note" name="note" placeholder="Ek bilgi var ise ekleyiniz..." rows="4" bind:value={readingForm.note}></textarea>
                        </div>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <div class="buttons">
                        <button class="button is-success" type="submit">Okuma Kaydet</button>
                        <button class="button" type="button" onclick={closeReading}>İptal</button>
                    </div>
                </footer>
            </form>
        </div>
    </div>

    <div class:is-active={chargeModal} class="modal">
        <button class="modal-background" type="button" aria-label="Kapat" onclick={closeCharge}></button>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{chargeResident ? `${chargeResident.name} ${chargeResident.lastname} : Okuma Bedeli Hazırla` : 'Okuma Bedeli Hazırla'}</p>
                <button class="delete" type="button" aria-label="Kapat" onclick={closeCharge}></button>
            </header>
            <section class="modal-card-body">
                {#if chargeMeter && chargeReading}
                    <div class="card">
                        <div class="card-content">
                            <div class="media">
                                <div class="media-left"><span class="icon is-large"><Gauge size={36} /></span></div>
                                <div class="media-content">
                                    <p class="title is-4">{chargeMeter.title}</p>
                                    <p class="subtitle is-6">{formatAmount(chargeMeter.rate)} {chargeMeter.unit} / {bina.pbirimi}</p>
                                </div>
                            </div>
                            <div class="content">
                                <table class="table is-fullwidth">
                                    <tbody>
                                        <tr>
                                            <th>Son Okuma - İlk Okuma</th>
                                            <td>{chargeReading.value} - {chargePreviousReading?.value ?? 0}</td>
                                        </tr>
                                        <tr>
                                            <th>Okuma Tarihleri</th>
                                            <td>{chargeReading.formatted_date} - {chargePreviousReading?.formatted_date ?? ''}</td>
                                        </tr>
                                        <tr>
                                            <th>Tutar</th>
                                            <td>{formatAmount(chargeAmount)} {bina.pbirimi}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                {/if}
            </section>
            <footer class="modal-card-foot">
                <div class="buttons">
                    {#if chargeReading}
                        <form action={`/sayac-okuma/${chargeReading.id}/bedel`} method="POST">
                            <input type="hidden" name="_token" value={csrfToken}>
                            <button class="button is-success" type="submit">Bedel Kaydet</button>
                        </form>
                    {/if}
                    <button class="button" type="button" onclick={closeCharge}>İptal</button>
                </div>
            </footer>
        </div>
    </div>
</Layout>

<style>
    .modal-card form {
        width: 100%;
    }
</style>
