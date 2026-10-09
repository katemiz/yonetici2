<script>
    import { Upload } from "@lucide/svelte";

    /**
     * Reusable Form Upload Component for Laravel-Inertia-Svelte 5
     * Supports single and multiple file uploads with preview
     *
     * @component
     * @example
     * <FormUpload
     *   form={form}
     *   name="avatar"
     *   label="Upload Avatar"
     *   accept="image/*"
     * />
     */

    let {
        form = $bindable(), // Inertia form object (required)
        name, // Field name (required)
        label = "", // Label text
        id = name, // Input id (defaults to name)
        required = false, // Required attribute
        disabled = false, // Disabled attribute
        multiple = false, // Allow multiple file selection
        accept = "", // Accepted file types (e.g., "image/*", ".pdf,.docx")
        maxSize = null, // Maximum file size in MB (null = no limit)
        showPreview = true, // Show file preview/thumbnails
        showFileInfo = true, // Show file name and size
        class: customClass = "", // Additional CSS classes
    } = $props();

    let fileInput;
    let previewUrls = $state([]);
    let fileNames = $state([]);

    function handleFileChange(event) {
        const files = Array.from(event.target.files || []);

        if (files.length === 0) {
            previewUrls = [];
            fileNames = [];
            return;
        }

        // Check file size if maxSize is specified
        if (maxSize) {
            const oversizedFiles = files.filter(
                (file) => file.size > maxSize * 1024 * 1024,
            );
            if (oversizedFiles.length > 0) {
                alert(`Some files exceed the maximum size of ${maxSize}MB`);
                return;
            }
        }

        // Store file information
        fileNames = files.map((file) => ({
            name: file.name,
            size: formatFileSize(file.size),
            type: file.type,
        }));

        // Generate preview URLs for images
        if (showPreview) {
            previewUrls = [];
            files.forEach((file) => {
                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewUrls = [...previewUrls, e.target.result];
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Update form data
        if (multiple) {
            form[name] = files;
        } else {
            form[name] = files[0];
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return (
            Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i]
        );
    }

    function clearFiles() {
        if (fileInput) {
            fileInput.value = "";
        }
        previewUrls = [];
        fileNames = [];
        form[name] = multiple ? [] : null;
    }

    function removeFile(index) {
        if (!multiple) {
            clearFiles();
            return;
        }

        // Remove from preview and file names
        previewUrls = previewUrls.filter((_, i) => i !== index);
        fileNames = fileNames.filter((_, i) => i !== index);

        // Update form data
        const files = Array.from(form[name] || []);
        files.splice(index, 1);
        form[name] = files;

        // Clear input if no files left
        if (files.length === 0 && fileInput) {
            fileInput.value = "";
        }
    }
</script>

<div class="field {customClass}">
    {#if label}
        <label class="label" for={id}>
            {label}
            {#if required}
                <span class="has-text-danger">*</span>
            {/if}
            {#if maxSize}
                <span class="has-text-grey is-size-7">(Max: {maxSize}MB)</span>
            {/if}
        </label>
    {/if}

    <div class="control">
        <div class="file has-name {form.errors[name] ? 'is-danger' : ''}">
            <label class="file-label">
                <input
                    bind:this={fileInput}
                    {id}
                    {name}
                    class="file-input"
                    type="file"
                    {required}
                    {disabled}
                    {multiple}
                    {accept}
                    onchange={handleFileChange}
                />
                <span class="file-cta">
                    <span class="file-icon">
                        <Upload size={18} />
                    </span>

                    <span class="file-label">
                        {multiple ? "Choose files…" : "Choose a file…"}
                    </span>
                </span>

                {#if fileNames.length > 0 && !multiple}
                    <span class="file-name">
                        {fileNames[0].name}
                    </span>
                {/if}
            </label>
        </div>
    </div>

    {#if fileNames.length > 0 && showFileInfo}
        <div class="mt-3">
            {#if multiple}
                <p class="has-text-weight-semibold mb-2">
                    Selected Files ({fileNames.length}):
                </p>
            {/if}

            {#each fileNames as file, index}
                <div class="box py-2 px-3 mb-2">
                    <div
                        class="is-flex is-justify-content-space-between is-align-items-center"
                    >
                        <div>
                            <p class="is-size-7 has-text-weight-semibold">
                                {file.name}
                            </p>
                            <p class="is-size-7 has-text-grey">{file.size}</p>
                        </div>
                        <button
                            type="button"
                            class="delete is-small"
                            onclick={() => removeFile(index)}
                            {disabled}
                            title="Remove"
                        ></button>
                    </div>
                </div>
            {/each}
        </div>
    {/if}

    {#if previewUrls.length > 0 && showPreview}
        <div class="mt-3">
            <p class="has-text-weight-semibold mb-2">Preview:</p>
            <div class="columns is-multiline">
                {#each previewUrls as url, index}
                    <div
                        class="column is-one-quarter-desktop is-one-third-tablet is-half-mobile"
                    >
                        <figure class="image is-square">
                            <img
                                src={url}
                                alt="Preview {index + 1}"
                                style="object-fit: cover;"
                            />
                        </figure>
                    </div>
                {/each}
            </div>
        </div>
    {/if}

    {#if fileNames.length > 0}
        <button
            type="button"
            class="button is-small is-light mt-2"
            onclick={clearFiles}
            {disabled}
        >
            Clear {multiple ? "All" : "File"}
        </button>
    {/if}

    {#if form.errors[name]}
        <p class="help is-danger">{form.errors[name]}</p>
    {:else}
        {#each Object.keys(form.errors) as errorKey}
            {#if errorKey.startsWith(`${name}.`)}
                <p class="help is-danger">{form.errors[errorKey]}</p>
            {/if}
        {/each}
    {/if}
</div>

<style>
    .file.is-danger .file-cta {
        border-color: #f14668;
    }
</style>
