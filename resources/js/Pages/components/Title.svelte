<script>
    /**
     * Reusable Form Checkbox Group Component for Laravel-Inertia-Svelte 5
     * Multiple checkboxes for selecting multiple values (array binding)
     *
     * @component
     * @example
     * <Title
     *   title={form}
     *   subtitle="interests"
     *   css1="class1 class 2"
     *   css1="class1 class 2"
     * />
     */

    import { page } from '@inertiajs/svelte';


    let { title, subtitle, css1,css2 } = $props();


    import {Settings} from '@lucide/svelte';

    let selectedBina = $derived(page?.props?.selected_bina ?? null);



</script>





<div class="column mb-6">
    {#if title}
        <h1 class="title {css1 ? css1 : 'has-text-weight-light is-size-1'}">
            {title}
        </h1>
    {/if}

    {#if subtitle}
        <h2 class="subtitle has-text-weight-light mt-1 {css2 ? css2 : ''}">
            {subtitle}
        </h2>
    {/if}
</div>


