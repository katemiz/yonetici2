<script>
    import { onMount } from 'svelte';
    import { FileUp } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, residents, period } = $props();
    let notes = $state('');
    let files = $state([]);
    let editorError = $state('');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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

            editor = await window.ClassicEditor.create(document.querySelector('#aidat-notes'));
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
</script>

<svelte:head>
    <title>Aylık Ödeme - Aidatlar - {bina?.name ?? 'Akıllı Yönetici'}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">Aylık Ödeme - Aidatlar</h1>
        <h2 class="subtitle">Toplu Aidat Kaydı Oluşturma</h2>

        <form action="/kayit-add/aidat" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_token" value={csrfToken}>
            <input type="hidden" name="editor_data" value={notes}>

            <div class="box">
                <div class="column field">
                    <label class="label" for="period">Ait olduğu dönem</label>
                    <div class="control">
                        <input class="input" id="period" type="date" name="donem" value={period} required>
                    </div>
                </div>

                <div class="column">
                    <div class="table-container">
                        <table class="table is-fullwidth">
                            <tbody>
                                {#each residents as resident}
                                    <tr>
                                        <td>{resident.door_no}</td>
                                        <td>{resident.name}</td>
                                        <td>{resident.lastname}</td>
                                        <td>Aylık aidat ödemesi</td>
                                        <td>{resident.amount}</td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="field" id="ck">
                    <label class="label" for="aidat-notes">Notlar</label>
                    <div class="column" id="aidat-notes"></div>
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
                    <button class="button is-link" type="submit">Kaydet</button>
                </div>
            </div>
        </form>
    </main>
</Layout>
