<script>
    import { page } from '@inertiajs/svelte';

    let app = $derived(page?.props?.app ?? {});
    let company = $derived(app.company ?? {});
</script>

<footer class="footer has-background-light">
    <div class="columns">
        <div class="column has-text-left has-text-centered-mobile">
            <img src={`/images/${company.logo ?? 'kapkara.svg'}`} width="28" alt={company.name ?? ''}>
            <br>
            <a href={company.link ?? '#'} class="has-text-weight-light">
                {company.name ?? ''}
            </a>
            <p class="has-text-weight-light is-size-7">
                {company.motto ?? ''}
            </p>
        </div>

        <div class="column has-text-centered has-text-centered-mobile">
            {app.title ?? 'Bina Yönetim Sistemi'}
        </div>

        <div class="column has-text-right has-text-centered-mobile">
            {app.copyright ?? ''}<br>
            <span class="is-size-7 has-text-grey">
                {app.version ?? ''}
            </span>
        </div>
    </div>
</footer>
