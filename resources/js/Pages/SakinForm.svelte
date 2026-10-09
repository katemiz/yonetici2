<script>
    import { onMount, untrack } from "svelte";
    import { ArrowLeft } from "@lucide/svelte";
    import Layout from "./Shared/Layout.svelte";

    let { bina, sakin = null, durum, oldInput = {}, errors = {} } = $props();
    let notes = $state(
        untrack(() => oldInput.editor_data ?? sakin?.remarks ?? ""),
    );
    const localDate = new Date();
    localDate.setMinutes(
        localDate.getMinutes() - localDate.getTimezoneOffset(),
    );
    const today = localDate.toISOString().slice(0, 10);
    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content ?? "";
    const title = $derived(sakin ? "Bilgi Güncelle" : "Bina Sakini Ekle");

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
                document.querySelector("#resident-notes"),
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

    let editorError = $state("");

    function value(field, fallback = "") {
        return oldInput?.[field] ?? fallback ?? "";
    }

    function isSelected(field, choice, fallback) {
        return String(value(field, fallback)) === String(choice);
    }

    function firstError(field) {
        const error = errors?.[field];
        return Array.isArray(error) ? error[0] : error;
    }
</script>

<svelte:head>
    <title>{title} - {bina.name}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <h1 class="title mt-6 has-text-weight-light is-size-1 has-text-left">
            {title}
        </h1>
        <h2 class="subtitle">{bina.name} Sakini</h2>

        <div class="mb-5">
            <a href={`/bina-view/${bina.id}`} class="icon-text">
                <span class="icon"><ArrowLeft size={18} /></span>
                <span>Geri</span>
            </a>
        </div>

        <form
            action={sakin
                ? `/sakin-update/${bina.id}/${sakin.id}`
                : `/sakin-add/${bina.id}`}
            method="POST"
        >
            <input type="hidden" name="_token" value={csrfToken} />
            <input type="hidden" name="editor_data" value={notes} />

            <div class="box">
                <div class="columns">
                    <div class="column field is-half">
                        <label class="label" for="resident-name">Ad</label>
                        <div class="control">
                            <input
                                class="input"
                                id="resident-name"
                                name="isim"
                                type="text"
                                placeholder="ad"
                                required
                                value={value("isim", sakin?.name)}
                            />
                        </div>
                        {#if firstError("isim")}<p class="help has-text-danger">
                                {firstError("isim")}
                            </p>{/if}
                    </div>

                    <div class="column field">
                        <label class="label" for="resident-surname">Soyad</label
                        >
                        <div class="control">
                            <input
                                class="input"
                                id="resident-surname"
                                name="soyisim"
                                type="text"
                                placeholder="soyad"
                                required
                                value={value("soyisim", sakin?.lastname)}
                            />
                        </div>
                        {#if firstError("soyisim")}<p
                                class="help has-text-danger"
                            >
                                {firstError("soyisim")}
                            </p>{/if}
                    </div>
                </div>

                <div class="columns">
                    <div class="column field is-half">
                        <label class="label" for="door-number"
                            >Kapı Numarası</label
                        >
                        <div class="control">
                            <input
                                class="input"
                                id="door-number"
                                name="door_no"
                                type="text"
                                placeholder="kapı numarası"
                                required
                                value={value("door_no", sakin?.door_no)}
                            />
                        </div>
                        {#if firstError("door_no")}<p
                                class="help has-text-danger"
                            >
                                {firstError("door_no")}
                            </p>{/if}
                    </div>

                    <fieldset class="column field">
                        <legend class="label">Ev Sahibi/Kiracı</legend>
                        <div class="control">
                            <label class="radio">
                                <input
                                    type="radio"
                                    name="sahiplik"
                                    value="1"
                                    required
                                    checked={isSelected(
                                        "sahiplik",
                                        "1",
                                        sakin?.is_evsahibi,
                                    )}
                                />
                                Ev Sahibi
                            </label>
                            <br />
                            <label class="radio">
                                <input
                                    type="radio"
                                    name="sahiplik"
                                    value="0"
                                    required
                                    checked={isSelected(
                                        "sahiplik",
                                        "0",
                                        sakin?.is_evsahibi,
                                    )}
                                />
                                Kiracı
                            </label>
                        </div>
                        {#if firstError("sahiplik")}<p
                                class="help has-text-danger"
                            >
                                {firstError("sahiplik")}
                            </p>{/if}
                    </fieldset>
                </div>

                <div class="columns">
                    <div class="column field is-half">
                        <label class="label" for="pay-ratio"
                            >Genel Ödeme Oranı (%)</label
                        >
                        <div class="control">
                            <input
                                class="input"
                                id="pay-ratio"
                                name="payratio"
                                type="number"
                                step="0.0001"
                                min="50"
                                max="100"
                                required
                                placeholder="100"
                                value={value(
                                    "payratio",
                                    sakin?.payratio ?? 100,
                                )}
                            />
                        </div>
                        {#if firstError("payratio")}<p
                                class="help has-text-danger"
                            >
                                {firstError("payratio")}
                            </p>{/if}
                    </div>
                    <div class="column notification is-warning is-light">
                        Bu değer diğer sakinlere oranla aidat gibi genel
                        ödemelere katılma oranını gösterir. m2 veya kişi
                        sayısına bağlı olarak değişebilir.
                        <p class="subtitle">[%50-%100 arasında olmalıdır]</p>
                    </div>
                </div>

                <div class="columns">
                    <div class="column field is-half">
                        <label class="label" for="phone">Telefon Numarası</label
                        >
                        <div class="control">
                            <input
                                class="input"
                                id="phone"
                                name="telno"
                                type="tel"
                                placeholder="telefon numarası"
                                required
                                value={value("telno", sakin?.phone)}
                            />
                        </div>
                        {#if firstError("telno")}<p
                                class="help has-text-danger"
                            >
                                {firstError("telno")}
                            </p>{/if}
                    </div>
                    <div class="column field">
                        <label class="label" for="email">E-Posta</label>
                        <div class="control">
                            <input
                                class="input"
                                id="email"
                                name="email"
                                type="email"
                                placeholder="E-posta adresi"
                                value={value("email", sakin?.email)}
                            />
                        </div>
                        {#if firstError("email")}<p
                                class="help has-text-danger"
                            >
                                {firstError("email")}
                            </p>{/if}
                    </div>
                    <div class="column field">
                        <label class="label" for="move-in-date"
                            >Giriş Tarihi</label
                        >
                        <div class="control">
                            <input
                                class="input"
                                id="move-in-date"
                                type="date"
                                name="giristarihi"
                                required
                                value={value(
                                    "giristarihi",
                                    sakin?.giris_tarihi ?? today,
                                )}
                            />
                        </div>
                        {#if firstError("giristarihi")}<p
                                class="help has-text-danger"
                            >
                                {firstError("giristarihi")}
                            </p>{/if}
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="resident-notes"
                        >Notes/Remarks</label
                    >
                    <div class="column" id="resident-notes"></div>
                    {#if editorError}<p
                            class="help has-text-danger"
                            role="alert"
                        >
                            {editorError}
                        </p>{/if}
                    {#if firstError("editor_data")}<p
                            class="help has-text-danger"
                        >
                            {firstError("editor_data")}
                        </p>{/if}
                </div>

                <fieldset class="field">
                    <legend class="label">Durum</legend>
                    <div class="control">
                        <label class="radio">
                            <input
                                type="radio"
                                name="status"
                                value="1"
                                required
                                checked={isSelected(
                                    "status",
                                    "1",
                                    sakin?.is_active,
                                )}
                            />
                            {durum["1"]}
                        </label>
                        <br />
                        <label class="radio">
                            <input
                                type="radio"
                                name="status"
                                value="0"
                                required
                                checked={isSelected(
                                    "status",
                                    "0",
                                    sakin?.is_active,
                                )}
                            />
                            {durum["0"]}
                        </label>
                    </div>
                    {#if firstError("status")}<p class="help has-text-danger">
                            {firstError("status")}
                        </p>{/if}
                </fieldset>

                <div class="buttons is-right">
                    <button class="button is-link" type="submit"
                        >{sakin ? "Güncelle" : "Kaydet"}</button
                    >
                </div>
            </div>
        </form>
    </main>
</Layout>
