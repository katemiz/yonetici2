<script lang="ts">
    import { Search, X } from '@lucide/svelte';

    // $bindable() allows the parent to use bind:query and lets this component update it (e.g., on clear)
    let {
        query = $bindable(""),
        placeholder = "Ara...",
        ariaLabel = "Binalarda ara"
    } = $props();

    // Reactively determine if the clear button should be shown
    let clearSearchIcon = $derived(query.length > 0);

    function handleClear() {
        query = "";
    }
</script>

<div class="control has-icons-right">
    <input
        bind:value={query}
        class="input"
        type="text"
        placeholder={placeholder}
        aria-label={ariaLabel}
    />
    
    {#if clearSearchIcon}
        <button
            type="button"
            class="icon is-small is-right is-clickable"
            onclick={handleClear}
            style="border:none; background:none;"
            aria-label="Aramayı temizle"
        >
            <X size="18" />
        </button>
    {:else}
        <span class="icon is-small is-right">
            <Search size="18" />
        </span>
    {/if}
</div>