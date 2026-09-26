import { page } from '@inertiajs/svelte';

export const auth = {
    get user() {
        return $page?.props?.auth?.user ?? null;
    },
    get isAuthenticated() {
        return this.user !== null;
    },
};
