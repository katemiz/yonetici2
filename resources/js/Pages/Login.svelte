<script>
    import { LogIn } from '@lucide/svelte';

    let { email = '', status = null, locale = 'tr', loginLogo, appName, company, labels, errors = {} } = $props();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function firstError(field) {
        const error = errors?.[field];
        return Array.isArray(error) ? error[0] : error;
    }
</script>

<svelte:head>
    <title>{labels.login} - {appName}</title>
</svelte:head>

<main class="login-page">
    <section class="section container is-max-desktop">
        <div class="column is-half is-offset-one-quarter">
            <nav class="breadcrumb has-bullet-separator is-right" aria-label="Language">
                <ul>
                    <li class:is-active={locale === 'tr'}><a href="/lang/tr">TR</a></li>
                    <li class:is-active={locale === 'en'}><a href="/lang/en">EN</a></li>
                </ul>
            </nav>
        </div>

        <div class="column is-half is-offset-one-quarter has-background-white login-card">
            <div class="column is-offset-3 is-offset-4-mobile is-6 is-4-mobile my-6">
                <figure class="image">
                    <img src={`/images/${loginLogo}`} alt={appName}>
                </figure>
            </div>

            {#if status}
                <div class="notification is-success is-light mb-4">{status}</div>
            {/if}

            {#if Object.keys(errors ?? {}).length > 0}
                <div class="notification is-danger is-light" role="alert">
                    {#each Object.values(errors).flat() as error}
                        <p>{error}</p>
                    {/each}
                </div>
            {/if}

            <form method="POST" action="/login" class="mx-4">
                <input type="hidden" name="_token" value={csrfToken}>

                <div class="field">
                    <label class="label has-text-link has-text-weight-light" for="email">{labels.email}</label>
                    <div class="control">
                        <input
                            class="input"
                            id="email"
                            type="email"
                            name="email"
                            value={email}
                            placeholder={labels.emailPlaceholder}
                            autocomplete="username"
                            required
                        >
                    </div>
                    {#if firstError('email')}
                        <p class="help is-danger">{firstError('email')}</p>
                    {/if}
                </div>

                <div class="field">
                    <label class="label has-text-link has-text-weight-light" for="password">{labels.password}</label>
                    <div class="control">
                        <input
                            class="input"
                            id="password"
                            type="password"
                            name="password"
                            placeholder={labels.passwordPlaceholder}
                            autocomplete="current-password"
                            required
                        >
                    </div>
                    {#if firstError('password')}
                        <p class="help is-danger">{firstError('password')}</p>
                    {/if}
                </div>

                <button class="button is-link mt-6 is-fullwidth" type="submit">
                    <span class="icon"><LogIn size={18} /></span>
                    <span>{labels.login}</span>
                </button>

                <a href="/resident-login" class="button is-link is-outlined mb-4 is-fullwidth mt-3">Bina Sakini Girişi</a>


                <div class="columns">
                    <div class="column is-half">
                        <p class="is-size-6 has-text-weight-light my-3">
                            <a href="/forgot-password">{labels.forgotPassword}</a>
                        </p>
                    </div>
                    <div class="column">
                        <p class="has-text-right is-size-6 has-text-weight-light my-3">
                            <a href="/register">{labels.register}</a>
                        </p>
                    </div>
                </div>




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
    .login-page {
        min-height: 100vh;
        background: #e6e6e6;
    }

    .login-card {
        background-color: white;
    }
</style>
