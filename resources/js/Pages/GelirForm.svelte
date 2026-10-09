<script>
    import { onMount, untrack } from "svelte";
    import { useForm } from "@inertiajs/svelte";
    import Layout from "./Shared/Layout.svelte";
    import FilesList from "./components/FilesList.svelte";
    import FormUpload from "./components/FormUpload.svelte";

    let { bina, kayit = null } = $props();
    let initialNotes = untrack(() => kayit?.remarks ?? "");
    let notes = $state(initialNotes);
    let form = $state(
        useForm({
            aciklama: untrack(() => kayit?.aciklama ?? ""),
            tutar: untrack(() => kayit?.tutar ?? ""),
            editor_data: initialNotes,
            dosyalar: [],
        }),
    );
    let editorError = $state("");

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
                document.querySelector("#income-notes"),
            );
            if (kayit?.remarks) {
                editor.setData(kayit.remarks);
                notes = kayit.remarks;
            }
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
        const error = form.errors?.[field];
        return Array.isArray(error) ? error[0] : error;
    }

    function submitForm(event) {
        event.preventDefault();
        form.editor_data = notes;
        form.post(
            kayit ? `/kayit-update/gelir/${kayit.id}` : "/kayit-add/gelir",
            { forceFormData: true },
        );
    }
</script>

<svelte:head>
    <title
        >{kayit ? "Gelir Kaydı Güncelle" : "Gelir Kaydı Ekle"} - {bina?.name ??
            "Akıllı Yönetici"}</title
    >
</svelte:head>

<Layout>
    <main class="section container">
        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">
            {kayit ? "Gelir Kaydı Güncelle" : "Gelir Kaydı Ekle"}
        </h1>
        <h2 class="subtitle">Gelir Kaydı</h2>

        <form onsubmit={submitForm}>
            <div class="box">
                <div class="columns">
                    <div class="column field is-half">
                        <label class="label" for="description">Açıklama</label>
                        <div class="control">
                            <input
                                class="input"
                                id="description"
                                type="text"
                                placeholder="Açıklama"
                                required
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
                </div>

                <div class="field" id="ck">
                    <label class="label" for="income-notes">Notlar</label>
                    <div class="column" id="income-notes"></div>
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
