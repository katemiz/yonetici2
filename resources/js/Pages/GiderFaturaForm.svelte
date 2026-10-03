<script>
    import { onMount, untrack } from 'svelte';
    import { useForm } from '@inertiajs/svelte';
    import FormUpload from './components/FormUpload.svelte';
    import FilesList from './components/FilesList.svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, tur, kayit = null, errors = {} } = $props();
    let initialNotes = untrack(() => kayit?.remarks ?? '');
    let notes = $state(initialNotes);
    let form = $state(useForm({
        aciklama: untrack(() => kayit?.aciklama ?? ''),
        tutar: untrack(() => kayit?.tutar ?? ''),
        spending_category: untrack(() => kayit?.spending_category ?? ''),
        sonodeme: untrack(() => kayit?.son_odeme ?? ''),
        editor_data: initialNotes,
        dosyalar: [],
    }));
    let editorError = $state('');
    const isInvoice = $derived(tur === 'fatura');
    const isExpense = $derived(tur === 'gider');
    const title = $derived(isInvoice
        ? (kayit ? 'Ödenecek Fatura Güncelle' : 'Ödenecek Fatura Ekle')
        : (kayit ? 'Gider Kaydı Güncelle' : 'Gider Kaydı Ekle'));
    const subtitle = $derived(isInvoice ? 'Ödenecek Fatura Kayıtları' : 'Gider Kaydı');

    onMount(() => {
        let editor;
        let cancelled = false;

        async function initializeEditor() {
            if (!window.ClassicEditor) {
                await new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = '/js/ckeditor5/ckeditor.js';
                    script.onload = resolve;
                    script.onerror = () => reject(new Error('Not editörü yüklenemedi.'));
                    document.head.appendChild(script);
                });
            }

            editor = await window.ClassicEditor.create(document.querySelector('#expense-notes'));
            if (kayit?.remarks) {
                editor.setData(kayit.remarks);
                notes = kayit.remarks;
            }
            if (cancelled) {
                await editor.destroy();
                return;
            }

            editor.model.document.on('change:data', () => {
                notes = editor.getData();
            });
        }

        initializeEditor().catch((error) => {
            editorError = 'Not editörü başlatılamadı.';
            console.error(error);
        });

        return () => {
            cancelled = true;
            editor?.destroy();
        };
    });

    function firstError(field) {
        const error = form.errors?.[field] ?? errors?.[field];
        return Array.isArray(error) ? error[0] : error;
    }

    function submitForm(event) {
        event.preventDefault();
        form.editor_data = notes;
        form.post(
            kayit ? `/kayit-update/${tur}/${kayit.id}` : `/kayit-add/${tur}`,
            { forceFormData: true },
        );
    }
</script>

<svelte:head>
    <title>{title} - {bina?.name ?? 'Akıllı Yönetici'}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">{title}</h1>
        <h2 class="subtitle">{subtitle}</h2>

        <form
            action={kayit ? `/kayit-update/${tur}/${kayit.id}` : `/kayit-add/${tur}`}
            method="POST"
            enctype="multipart/form-data"
            onsubmit={submitForm}
        >
            <input type="hidden" name="editor_data" value={notes}>

            <div class="box">
                <div class="columns">
                    <div class="column field is-half">
                        <label class="label" for="description">Açıklama</label>
                        <div class="control">
                            <input
                                class="input"
                                id="description"
                                name="aciklama"
                                type="text"
                                placeholder="Açıklama"
                                list="expense-items"
                                required
                                bind:value={form.aciklama}
                            >
                            <datalist id="expense-items">
                                {#each bina.kalemler as kalem}
                                    <option value={kalem}></option>
                                {/each}
                            </datalist>
                        </div>
                        {#if firstError('aciklama')}
                            <p class="help has-text-danger">{firstError('aciklama')}</p>
                        {/if}
                    </div>

                    <div class="column field">
                        <label class="label" for="spending-category">Harcama Kategorisi</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="spending-category" name="spending_category" bind:value={form.spending_category} required>
                                    <option value="">Kategori seçiniz</option>
                                    {#each ['Isınma', 'Su', 'Elektrik', 'Temizlik', 'Diğer'] as category}
                                        <option value={category}>{category}</option>
                                    {/each}
                                </select>
                            </div>
                        </div>
                        {#if firstError('spending_category')}
                            <p class="help has-text-danger">{firstError('spending_category')}</p>
                        {/if}
                    </div>

                    <div class="column field">
                        <label class="label" for="amount">Tutar, {bina.pbirimi}</label>
                        <div class="control">
                            <input
                                class="input"
                                id="amount"
                                name="tutar"
                                type="text"
                                placeholder="650,25 örnek"
                                required
                                bind:value={form.tutar}
                            >
                        </div>
                        {#if firstError('tutar')}
                            <p class="help has-text-danger">{firstError('tutar')}</p>
                        {/if}
                    </div>

                    {#if isInvoice}
                        <div class="column field is-3 has-text-right">
                            <label class="label" for="due-date">Son Ödeme</label>
                            <div class="control">
                                <input
                                    class="input"
                                    id="due-date"
                                    name="sonodeme"
                                    type="date"
                                    required
                                    bind:value={form.sonodeme}
                                >
                            </div>
                            {#if firstError('sonodeme')}
                                <p class="help has-text-danger">{firstError('sonodeme')}</p>
                            {/if}
                        </div>
                    {/if}
                </div>

                <div class="field" id="ck">
                    <label class="label" for="expense-notes">Notlar</label>
                    <div class="column" id="expense-notes"></div>
                    {#if editorError}
                        <p class="help has-text-danger" role="alert">{editorError}</p>
                    {/if}
                </div>

                <div class="column box mt-6">
                    {#if kayit?.files?.length}
                        <FilesList media={kayit.files} />
                    {/if}
                    <FormUpload bind:form name="dosyalar" label="Dosyalar" multiple maxSize={10} />
                </div>

                <div class="buttons is-right">
                    <button class="button is-link" type="submit" disabled={form.processing}>{kayit ? 'Güncelle' : 'Kaydet'}</button>
                </div>
            </div>
        </form>
    </main>
</Layout>
