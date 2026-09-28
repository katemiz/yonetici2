<script>
    import { onMount, untrack } from 'svelte';
    import { FileUp } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, residents, kayit = null, oldInput = {}, errors = {} } = $props();
    let notes = $state(untrack(() => oldInput.editor_data ?? kayit?.remarks ?? ''));
    let files = $state([]);
    let editorError = $state('');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const title = $derived(kayit ? 'Alacak Kaydı Güncelle' : 'Alacak Kaydı Ekle');

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

            editor = await window.ClassicEditor.create(document.querySelector('#receivable-notes'));
            editor.setData(notes);
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

    function fieldValue(field, fallback) {
        return oldInput?.[field] ?? fallback ?? '';
    }
</script>

<svelte:head>
    <title>{title} - {bina?.name ?? 'Akıllı Yönetici'}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">{title}</h1>
        <h2 class="subtitle">Alacak Kayıtları</h2>

        <form action={kayit ? `/kayit-update/alacak/${kayit.id}` : '/kayit-add/alacak'} method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_token" value={csrfToken}>
            <input type="hidden" name="editor_data" value={notes}>

            <div class="box">
                <div class="columns">
                    <div class="column field">
                        <label class="label" for="debtor">Borçlu Sakin</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="debtor" name="borclu" required>
                                    <option value="">Sakin seçiniz</option>
                                    {#each residents as resident}
                                        {@const selectedResident = String(fieldValue('borclu', kayit?.sakin_id)) === String(resident.id)}
                                        <option value={resident.id} selected={selectedResident}>
                                            [ No {resident.door_no} ] {resident.name} {resident.lastname}
                                        </option>
                                    {/each}
                                </select>
                            </div>
                        </div>
                        {#if firstError('borclu')}
                            <p class="help has-text-danger">{firstError('borclu')}</p>
                        {/if}
                    </div>

                    <div class="column field is-half">
                        <label class="label" for="description">Açıklama</label>
                        <div class="control">
                            <input
                                class="input"
                                id="description"
                                name="aciklama"
                                type="text"
                                placeholder="Açıklama"
                                required
                                minlength="10"
                                value={fieldValue('aciklama', kayit?.aciklama)}
                            >
                        </div>
                        {#if firstError('aciklama')}
                            <p class="help has-text-danger">{firstError('aciklama')}</p>
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
                                inputmode="decimal"
                                placeholder="650,25 örnek"
                                required
                                value={fieldValue('tutar', kayit?.tutar)}
                            >
                        </div>
                        {#if firstError('tutar')}
                            <p class="help has-text-danger">{firstError('tutar')}</p>
                        {/if}
                    </div>

                    <div class="column field is-3 has-text-right">
                        <label class="label" for="due-date">Son Ödeme</label>
                        <div class="control">
                            <input
                                class="input"
                                id="due-date"
                                name="sonodeme"
                                type="date"
                                value={fieldValue('sonodeme', kayit?.son_odeme)}
                            >
                        </div>
                        {#if firstError('sonodeme')}
                            <p class="help has-text-danger">{firstError('sonodeme')}</p>
                        {/if}
                    </div>
                </div>

                <div class="field" id="ck">
                    <label class="label" for="receivable-notes">Notlar</label>
                    <div class="column" id="receivable-notes"></div>
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
