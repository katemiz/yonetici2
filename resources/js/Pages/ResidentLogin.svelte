<script>
    import { LogIn } from '@lucide/svelte';

    let { phone = '', locale = 'tr', loginLogo, appName, company, errors = {} } = $props();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function firstError(field) {
        const error = errors?.[field];
        return Array.isArray(error) ? error[0] : error;
    }
</script>

<svelte:head>
    <title>Bina Sakini Girişi - {appName}</title>
</svelte:head>

<main class="resident-login-page">
    <section class="section container is-max-desktop">
        <div class="column is-half is-offset-one-quarter">
            <nav class="breadcrumb has-bullet-separator is-right" aria-label="Dil seçimi">
                <ul>
                    <li class:is-active={locale === 'tr'}><a href="/lang/tr">TR</a></li>
                    <li class:is-active={locale === 'en'}><a href="/lang/en">EN</a></li>
                </ul>
            </nav>
        </div>

        <div class="column is-half is-offset-one-quarter has-background-white resident-login-card">
            <div class="column is-offset-3 is-offset-4-mobile is-6 is-4-mobile my-6">
                <figure class="image">
                    <img src={`/images/${loginLogo}`} alt={appName}>
                </figure>
            </div>

            {#if Object.keys(errors ?? {}).length > 0}
                <div class="notification is-danger is-light" role="alert">
                    {#each Object.values(errors).flat() as error}
                        <p>{error}</p>
                    {/each}
                </div>
            {/if}

            <form method="POST" action="/resident-login" class="mx-4">
                <input type="hidden" name="_token" value={csrfToken}>

                <div class="field">
                    <label class="label" for="phone">Telefon numarası</label>
                    <div class="control">
                        <input
                            id="phone"
                            name="phone"
                            class="input"
                            type="tel"
                            value={phone}
                            placeholder="+90 5xx xxx xx xx"
                            autocomplete="tel"
                            required
                        >
                    </div>
                    {#if firstError('phone')}
                        <p class="help is-danger">{firstError('phone')}</p>
                    {/if}
                </div>

                <div class="field">
                    <label class="label" for="building_code">Kapı Giriş Şifresi</label>
                    <div class="control">
                        <input
                            id="building_code"
                            name="building_code"
                            class="input"
                            type="password"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                    {#if firstError('building_code')}
                        <p class="help is-danger">{firstError('building_code')}</p>
                    {/if}
                </div>

                <button class="button is-link mt-6 mb-2 is-fullwidth" type="submit">
                    <span class="icon"><LogIn size={18} /></span>
                    <span>Giriş yap</span>
                </button>

                <a href="/login" class="button is-link is-outlined is-fullwidth mb-4">Yönetici girişi</a>
            </form>
        </div>

        <div class="column is-half is-offset-one-quarter">
            <a href={company.link} class="button is-small is-ghost">
                <span class="icon is-small">
                    <img src={`/images/${company.logo}`} alt={company.name}>
                </span>
                <span>{company.name}</span>
            </a>
        </div>
    </section>
</main>

<style>
    .resident-login-page {
        min-height: 100vh;
        background: #e6e6e6;
    }

    .resident-login-card {
        background-color: white;
    }
</style>
