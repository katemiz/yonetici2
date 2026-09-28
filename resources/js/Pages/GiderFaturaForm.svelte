<script>
    import { onMount } from 'svelte';
    import { FileUp } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, tur, kayit = null, errors = {} } = $props();
    let notes = $state('');
    let files = $state([]);
    let editorError = $state('');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const isInvoice = $derived(tur === 'fatura');
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

    function updateFiles(event) {
        files = Array.from(event.currentTarget.files ?? []);
    }

    function firstError(field) {
        const error = errors?.[field];
        return Array.isArray(error) ? error[0] : error;
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
        >
            <input type="hidden" name="_token" value={csrfToken}>
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
                                value={kayit?.aciklama ?? ''}
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
                                <select id="spending-category" name="spending_category" required>
                                    <option value="">Kategori seçiniz</option>
                                    {#each ['Isınma', 'Su', 'Elektrik', 'Temizlik', 'Diğer'] as category}
                                        <option value={category} selected={kayit?.spending_category === category}>{category}</option>
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
                                value={kayit?.tutar ?? ''}
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
                                    value={kayit?.son_odeme ?? ''}
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
                    <div class="columns">
                        <div class="column is-2">
                            <div class="file is-boxed">
                                <label class="file-label">
                                    <input class="file-input" type="file" name="dosyalar[]" multiple onchange={updateFiles}>
                                    <span class="file-cta">
                                        <span class="file-icon"><FileUp size={20} /></span>
                                        <span class="file-label">Dosyalar</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="column">
                            <table class="table is-striped is-fullwidth">
                                <tbody>
                                    {#each files as file}
                                        <tr>
                                            <td>{file.name}</td>
                                            <td>{file.size}</td>
                                            <td>{file.type}</td>
                                        </tr>
                                    {/each}
                                </tbody>
                                {#if files.length === 0}
                                    <tfoot>
                                        <tr><td colspan="4" class="has-text-centered">Henüz seçilmiş dosya yok!</td></tr>
                                    </tfoot>
                                {/if}
                            </table>
                        </div>
                    </div>
                </div>

                <div class="buttons is-right">
                    <button class="button is-link" type="submit">{kayit ? 'Güncelle' : 'Kaydet'}</button>
                </div>
            </div>
        </form>
    </main>
</Layout>
