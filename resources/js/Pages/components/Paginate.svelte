<script>
    import { Link } from "@inertiajs/svelte";
    import { ChevronLeft, ChevronRight } from "@lucide/svelte";

    let { items } = $props();

    // 1. Get all links
    let allLinks = $derived(items.links || []);

    // 2. Identify the specific Prev/Next objects from Laravel
    let prevLink = $derived(allLinks[0]);
    let nextLink = $derived(allLinks[allLinks.length - 1]);

    // 3. Filter out the Prev/Next from the middle number list
    let pageLinks = $derived(allLinks.slice(1, -1));
</script>

{#if items.total > items.per_page}
    <nav class="pagination is-centered">
        {#if prevLink?.url}
            <Link
                href={prevLink.url}
                class="pagination-previous"
                preserveScroll
                preserveState
            >
                <ChevronLeft size="18" />
            </Link>
        {:else}
            <span
                class="pagination-previous is-disabled"
                title="This is the first page"
            >
                <ChevronLeft size="18" />
            </span>
        {/if}

        {#if nextLink?.url}
            <Link
                href={nextLink.url}
                class="pagination-next"
                preserveScroll
                preserveState
            >
                <ChevronRight size="18" />
            </Link>
        {:else}
            <span
                class="pagination-next is-disabled"
                title="This is the last page"
            >
                <ChevronRight size="18" />
            </span>
        {/if}

        <ul class="pagination-list">
            {#each pageLinks as link}
                <li>
                    {#if link.url === null}
                        <span class="pagination-ellipsis">&hellip;</span>
                    {:else}
                        <Link
                            href={link.url}
                            class="pagination-link {link.active
                                ? 'is-current'
                                : ''}"
                            preserveScroll
                            preserveState
                        >
                            {@html link.label}
                        </Link>
                    {/if}
                </li>
            {/each}
        </ul>
    </nav>
{/if}
