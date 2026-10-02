<script>
    import { page } from '@inertiajs/svelte';
    import {
        BanknoteArrowDown,
        BanknoteArrowUp,
        Building2,
        ChartBar,
        CircleHelp,
        FileText,
        LogIn,
        LogOut,
        Printer,
        SquarePen,
        TurkishLira,
        ReceiptTurkishLira,
        Database,
        Settings,
    } from '@lucide/svelte';

    let iconColor = '#f9c80e';

    let user = $derived(page?.props?.auth?.user ?? null);
    let userType = $derived(page?.props?.userType ?? 'guest');
    let isResident = $derived(userType === 'resident');
    let resident = $derived(page?.props?.resident ?? null);
    let selectedBina = $derived(page?.props?.selected_bina ?? null);
    let userName = $derived(user ? `${user.name ?? ''} ${user.lastname ?? ''}`.trim() : '');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
</script>

<nav class="navbar is-dark" aria-label="main navigation">
    <div class="container is-fluid">
        <div class="navbar-brand">
            <a href="/" class="navbar-item">
                <img src="/images/app_header_logo.svg" alt="Akıllı Yönetici">
            </a>
        </div>
        {#if isResident}
            <div class="navbar-menu is-active">
                <div class="navbar-start">
                    <a href="/resident-status/ozet" class="navbar-item">Genel Durum</a>
                    <a href="/resident-status/alacaklar" class="navbar-item">Alacaklar</a>
                    <a href="/resident-status/gelirler" class="navbar-item">Gelirler</a>
                    <a href="/resident-status/giderler" class="navbar-item">Giderler</a>
                </div>
                <div class="navbar-end">
                    {#if resident?.name}
                        <div class="navbar-item user-summary">
                            <span>{resident.name}</span>
                            <small class="is-size-6 has-text-grey-light">Bina Sakini [{resident.door_no}]</small>
                        </div>
                    {/if}
                    <form action="/resident-logout" method="POST" class="navbar-item">
                        <input type="hidden" name="_token" value={csrfToken}>
                        <button type="submit" class="button is-light">Çıkış</button>
                    </form>
                </div>
            </div>
        {:else if user}
            <div class="navbar-menu is-active">
                <div class="navbar-start">
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link" href="/durum/ozet">
                        Durum
                        </a>
                        <div class="navbar-dropdown">
                            <a href="/durum/ozet" class="navbar-item">Genel Özet</a>
                            <a href="/durum/alacaklar" class="navbar-item">Alacaklar</a>
                            <a href="/durum/verecekler" class="navbar-item">Verecekler</a>
                        </div>
                    </div>
                    <a href="/durum/gelirler" class="navbar-item">
                    <BanknoteArrowDown class="menu-icon" color="{iconColor}"/>Gelir
                    </a>
                    <a href="/durum/giderler" class="navbar-item">
                    <BanknoteArrowUp class="menu-icon" color="{iconColor}"/>Gider</a>
                    <a href="/durum/verecekler" class="navbar-item">
                    <ReceiptTurkishLira class="menu-icon" color="{iconColor}"/>Faturalar</a>
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link" href="/kayit-form/aidat">Kayıtlar</a>
                        <div class="navbar-dropdown">
                            <a href="/kayit-form/aidat" class="navbar-item">Toplu Aidat Kaydı</a>
                            <a href="/kayit-form/alacak" class="navbar-item">Alacak Kaydı</a>
                            <a href="/kayit-form/fatura" class="navbar-item">Fatura Kaydı</a>
                            <a href="/kayit-form/gelir" class="navbar-item">Gelir Kaydı</a>
                            <a href="/kayit-form/gider" class="navbar-item">Gider Kaydı</a>
                            <a href="/sayac-okuma" class="navbar-item">Sayaç Okumaları</a>
                        </div>
                    </div>
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link" href="/dokum">Yazdır</a>
                        <div class="navbar-dropdown">
                            <a href="/dokum" class="navbar-item">Gelir-Gider Döküm</a>
                            <a href="/aylik-aidatlar" class="navbar-item">Aylık Aidatlar</a>
                            <a href="/bosmakbuz" class="navbar-item">Boş Makbuz</a>
                        </div>
                    </div>
                    {#if userType === 'superuser'}
                        <a href="/admin/managers" class="navbar-item">Yöneticiler</a>
                    {/if}
                </div>
                <div class="navbar-end">
                    <div class="navbar-item has-dropdown is-hoverable is-gap-0">
                        <a class="navbar-link" href="/bina-list">
                            {userName}
                        </a>
                        <div class="navbar-dropdown is-right">
                            <a href="/bina-list" class="navbar-item">
                                <Building2 size={18} color="blue"/>Binalarım
                            </a>
                            <a href="/help" class="navbar-item">
                                <CircleHelp size={18} color="blue"/>
                                Yardım
                            </a>

                            <form action="/logout" method="POST">
                                <input type="hidden" name="_token" value={csrfToken}>
                                <button type="submit" class="navbar-item">
                                    <span class="icon">
                                        <LogOut size={18} color="blue" />
                                    </span>
                                    <span>Çıkış</span>
                                </button>
                            </form>

                        </div>


                    </div>






                </div>
            </div>
        {:else}
            <div class="navbar-end">
                <a href="/login" class="navbar-item">
                    <LogIn class="menu-icon" />Giriş
                </a>
            </div>
        {/if}
    </div>
</nav>


{#if selectedBina}
<div class="has-background-light is-size-7 has-text-warning has-text-right p-1 has-border">
  <a class="button is-ghost py-0" href="/bina-view/{selectedBina}">
    <span class="icon">
      <Settings size="16" color="blue" />
    </span>
    <span>{selectedBina}</span>
  </a>
</div>

{/if}

<style>
    :global(.menu-icon) {
        color: hsl(217, 71%, 35%);
        width: 1.2em;
        height: 1.2em;
        margin-right: 0.4rem;
    }


    /* Force override Bulma's navbar-link padding and line-height for this specific element */
    :global(.tight-user-summary) {
        display: flex !important;
        flex-direction: column !important;
        justify-content: right !important;
        gap: 0.25rem !important; /* Adjust this value (e.g., 0, 0.15rem, 0.25rem) to get the exact spacing you want */
        line-height: 1.1 !important;
        padding-top: 0.35rem !important;    /* Reduce Bulma's default 0.5rem padding */
        padding-bottom: 0.35rem !important; /* Reduce Bulma's default 0.5rem padding */
    }

    .has-border {
  border-bottom: 1px solid #dbdbdb; /* Standard Bulma border color */
}
</style>
