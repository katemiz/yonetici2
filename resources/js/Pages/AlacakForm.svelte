<script>
    import { onMount, untrack } from "svelte";
    import { useForm } from "@inertiajs/svelte";
    import FilesList from "./components/FilesList.svelte";
    import FormUpload from "./components/FormUpload.svelte";
    import Layout from "./Shared/Layout.svelte";

    let {
        bina,
        residents,
        kayit = null,
        oldInput = {},
        errors = {},
    } = $props();
    let initialNotes = untrack(
        () => oldInput.editor_data ?? kayit?.remarks ?? "",
    );
    let notes = $state(initialNotes);
    let form = $state(
        useForm({
            borclu: String(
                untrack(() => oldInput.borclu ?? kayit?.sakin_id ?? ""),
            ),
            aciklama: untrack(() => oldInput.aciklama ?? kayit?.aciklama ?? ""),
            tutar: untrack(() => oldInput.tutar ?? kayit?.tutar ?? ""),
            sonodeme: untrack(
                () => oldInput.sonodeme ?? kayit?.son_odeme ?? "",
            ),
            editor_data: initialNotes,
            dosyalar: [],
        }),
    );
    let editorError = $state("");
    const title = $derived(
        kayit ? "Alacak Kaydı Güncelle" : "Alacak Kaydı Ekle",
    );

    onMount(() => {
        let editor;
        let cancelled = false;

        async function initializeEditor() {
            if (!window.ClassicEditor) {
                await new Promise((resolve, reject) => {
                    const script = document.createElement("script");
                    script.src = "/js/ckeditor5/ckeditor.js";
                    script.onload = resolve;
                    script.onerror = () =>
                        reject(new Error("Not editörü yüklenemedi."));
                    document.head.appendChild(script);
                });
            }

            editor = await window.ClassicEditor.create(
                document.querySelector("#receivable-notes"),
            );
            editor.setData(notes);
            if (cancelled) {
                await editor.destroy();
                return;
            }

            editor.model.document.on("change:data", () => {
                notes = editor.getData();
            });
        }

        initializeEditor().catch((error) => {
            editorError = "Not editörü başlatılamadı.";
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
            kayit ? `/kayit-update/alacak/${kayit.id}` : "/kayit-add/alacak",
            { forceFormData: true },
        );
    }
</script>

<svelte:head>
    <title>{title} - {bina?.name ?? "Akıllı Yönetici"}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">
            {title}
        </h1>
        <h2 class="subtitle">Alacak Kayıtları</h2>

        <form onsubmit={submitForm}>
            <div class="box">
                <div class="columns">
                    <div class="column field">
                        <label class="label" for="debtor">Borçlu Sakin</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select
                                    id="debtor"
                                    bind:value={form.borclu}
                                    required
                                >
                                    <option value="">Sakin seçiniz</option>
                                    {#each residents as resident}
                                        <option value={String(resident.id)}>
                                            [ No {resident.door_no} ] {resident.name}
                                            {resident.lastname}
                                        </option>
                                    {/each}
                                </select>
                            </div>
                        </div>
                        {#if firstError("borclu")}
                            <p class="help has-text-danger">
                                {firstError("borclu")}
                            </p>
                        {/if}
                    </div>

                    <div class="column field is-half">
                        <label class="label" for="description">Açıklama</label>
                        <div class="control">
                            <input
                                class="input"
                                id="description"
                                type="text"
                                placeholder="Açıklama"
                                required
                                minlength="10"
                                bind:value={form.aciklama}
                            />
                        </div>
                        {#if firstError("aciklama")}
                            <p class="help has-text-danger">
                                {firstError("aciklama")}
                            </p>
                        {/if}
                    </div>

                    <div class="column field">
                        <label class="label" for="amount"
                            >Tutar, {bina.pbirimi}</label
                        >
                        <div class="control">
                            <input
                                class="input"
                                id="amount"
                                type="text"
                                inputmode="decimal"
                                placeholder="650,25 örnek"
                                required
                                bind:value={form.tutar}
                            />
                        </div>
                        {#if firstError("tutar")}
                            <p class="help has-text-danger">
                                {firstError("tutar")}
                            </p>
                        {/if}
                    </div>

                    <div class="column field is-3 has-text-right">
                        <label class="label" for="due-date">Son Ödeme</label>
                        <div class="control">
                            <input
                                class="input"
                                id="due-date"
                                type="date"
                                bind:value={form.sonodeme}
                            />
                        </div>
                        {#if firstError("sonodeme")}
                            <p class="help has-text-danger">
                                {firstError("sonodeme")}
                            </p>
                        {/if}
                    </div>
                </div>

                <div class="field" id="ck">
                    <label class="label" for="receivable-notes">Notlar</label>
                    <div class="column" id="receivable-notes"></div>
                    {#if editorError}
                        <p class="help has-text-danger" role="alert">
                            {editorError}
                        </p>
                    {/if}
                </div>

                {#if kayit?.files?.length}
                    <div class="column box mt-6">
                        <FilesList media={kayit.files} />
                    </div>
                {/if}

                <div class="column box mt-6">
                    <FormUpload
                        bind:form
                        name="dosyalar"
                        label="Dosyalar"
                        multiple
                        maxSize={10}
                    />
                </div>

                <div class="buttons is-right">
                    <button
                        class="button is-link"
                        type="submit"
                        disabled={form.processing}
                        >{kayit ? "Güncelle" : "Kaydet"}</button
                    >
                </div>
            </div>
        </form>
    </main>
</Layout>
