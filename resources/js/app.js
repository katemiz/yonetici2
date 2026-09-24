import './bootstrap';

import Alpine from 'alpinejs';
import { createInertiaApp } from '@inertiajs/svelte';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

window.Alpine = Alpine;

Alpine.start();

import Swal from 'sweetalert2'

window.Swal = Swal

createInertiaApp({
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.svelte`,
        import.meta.glob('./Pages/**/*.svelte'),
    ),
    setup({ el, App, props }) {
        new App({ target: el, props });
    },
});
