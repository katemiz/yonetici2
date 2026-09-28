<script>
    import { ArrowLeft, Building2, Pencil, Settings, Users } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina } = $props();
</script>

<svelte:head>
    <title>{bina.name} - Akıllı Yönetici</title>
</svelte:head>

<Layout>
    <main class="section container">
        <h1 class="title has-text-weight-light">{bina.name}</h1>
        <h2 class="subtitle">Özellikler</h2>

        <a href="/bina-list" class="icon-text">
            <span class="icon"><ArrowLeft size={18} /></span>
            <span>Geri</span>
        </a>

        <div class="columns mt-6">
            <div class="column is-3">
                <aside class="menu">
                    <p class="menu-label">Menü</p>
                    <ul class="menu-list">
                        <li>
                            <a href={`/sakin-list/${bina.id}`}>
                                <span class="icon-text"><span class="icon"><Users size={18} /></span><span>Bina Sakinleri ({bina.sakinler_count})</span></span>
                            </a>
                        </li>
                        <li>
                            <a href={`/bedel-list/${bina.id}`}>
                                <span class="icon-text"><span class="icon"><Settings size={18} /></span><span>Hizmet Bedelleri ({bina.bedeller_count})</span></span>
                            </a>
                        </li>
                        <li>
                            <a href={`/kalem-list/${bina.id}`}>
                                <span class="icon-text"><span class="icon"><Settings size={18} /></span><span>Harcama Türleri ({bina.kalemler_count})</span></span>
                            </a>
                        </li>
                        <li>
                            <a href={`/sayac-okuma/${bina.id}`}>
                                <span class="icon-text"><span class="icon"><Building2 size={18} /></span><span>Sayaç Okumaları</span></span>
                            </a>
                        </li>
                    </ul>
                </aside>
            </div>

            <div class="column">
                <article class="card mb-6">
                    <div class="card-content">
                        <div class="media">
                            <div class="media-content">
                                <p class="title is-4">{bina.name}</p>
                            </div>
                        </div>
                        <div class="content">
                            {bina.address}
                            <p class="mt-3">Kullanılan Para Birimi: {bina.pbirimi}</p>
                            <p class="mt-3">
                                <strong>Bina sakini ortak giriş kodu:</strong>
                                {#if bina.resident_access_configured}
                                    <span class="has-text-success">Yapılandırılmış</span>
                                {:else}
                                    <span class="has-text-warning-dark">Tanımlanmamış</span>
                                {/if}
                            </p>
                        </div>
                    </div>
                    <footer class="card-footer">
                        <a href={`/bina-form/${bina.id}`} class="card-footer-item">
                            <Pencil size={18} />&nbsp;Değiştir
                        </a>
                    </footer>
                </article>

                <nav class="level">
                    <div class="level-left">{bina.created_at}</div>
                    <div class="level-right">{bina.created_human}</div>
                </nav>
            </div>
        </div>
    </main>
</Layout>
