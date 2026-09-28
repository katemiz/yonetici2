import './bootstrap';

import Alpine from 'alpinejs';
import { createInertiaApp } from '@inertiajs/svelte';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { mount } from 'svelte';

window.Alpine = Alpine;

Alpine.start();

import Swal from 'sweetalert2'

window.Swal = Swal

const appElement = document.getElementById('app');
const initialPage = appElement?.dataset.page;

if (appElement && !initialPage) {
    throw new Error('Inertia initial page payload is missing.');
}

if (initialPage) {
    createInertiaApp({
        page: JSON.parse(initialPage),
        resolve: (name) => resolvePageComponent(
            `./Pages/${name}.svelte`,
            import.meta.glob('./Pages/**/*.svelte'),
        ),
        setup({ el, App, props }) {
            mount(App, { target: el, props });
        },
    });
}
