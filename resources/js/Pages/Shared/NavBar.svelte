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
        User,
    } from '@lucide/svelte';

    let user = $derived(page?.props?.auth?.user ?? null);
    let userType = $derived(page?.props?.userType ?? 'guest');
    let isResident = $derived(userType === 'resident');
    let resident = $derived(page?.props?.resident ?? null);
    let selectedBina = $derived(page?.props?.selected_bina ?? null);
    let userName = $derived(user ? `${user.name ?? ''} ${user.lastname ?? ''}`.trim() : '');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
</script>

<nav class="navbar is-light" aria-label="main navigation">
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
                    <BanknoteArrowDown class="menu-icon" />Gelir
                    </a>
                    <a href="/durum/giderler" class="navbar-item">
                    <BanknoteArrowUp class="menu-icon" />Gider</a>
                    <a href="/durum/verecekler" class="navbar-item">
                    <ReceiptTurkishLira class="menu-icon" />Faturalar</a>
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
                </div>
                <div class="navbar-end">
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link user-summary" href="/bina-list">
                            <span>{userName}</span>
                            {#if selectedBina}
                                <span class="is-size-6 has-text-grey-light"><small>{selectedBina}</small></span>
                            {/if}
                        </a>
                        <div class="navbar-dropdown is-right">
                            <a href="/bina-list" class="navbar-item">
                                <Building2 size={18} />Binalarım
                            </a>
                            <a href="/help" class="navbar-item">
                                <CircleHelp size={18} />
                                Yardım
                            </a>

                            <form action="/logout" method="POST">
                                <input type="hidden" name="_token" value={csrfToken}>
                                <button type="submit" class="navbar-item">
                                    <span class="icon">
                                        <LogOut size={18} />
                                    </span>
                                    <span>Çıkış (Logout)</span>
                                </button>
                            </form>

                        </div>


                    </div>






                </div>
            </div>
        {:else}
            <div class="navbar-end">
                <a href="/login" class="navbar-item"><LogIn class="menu-icon" />Giriş</a>
                <a href="/register" class="navbar-item"><User class="menu-icon" />Kaydolun</a>
            </div>
        {/if}
    </div>
</nav>

<style>
    :global(.menu-icon) {
        color: hsl(217, 71%, 35%);
        width: 1.2em;
        height: 1.2em;
        margin-right: 0.4rem;
    }

    :global(.user-summary) {
        align-items: flex-end;
        flex-direction: column;
        justify-content: center;
    }
</style>
