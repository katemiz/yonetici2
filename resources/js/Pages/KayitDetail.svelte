<script>
    import { FileText, Pencil } from '@lucide/svelte';
    import Layout from './Shared/Layout.svelte';

    let { bina, record } = $props();
    let breakdown = $derived(record.breakdown ?? {});
</script>

<svelte:head>
    <title>Kayıt {record.id} - {bina?.name ?? 'Akıllı Yönetici'}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <div class="fixed-grid">
            <div class="grid mb-6">
                <div class="cell">
                    <h1 class="title has-text-weight-light is-size-1">Kayıt Bilgileri</h1>
                    <h2 class="subtitle has-text-weight-light">Kayıda Ait Olan Tüm Veriler</h2>
                </div>
                <div class="cell has-text-right record-actions">
                    <a href={`/makbuzpdf/${record.id}`} class="icon mr-4" aria-label="PDF indir" title="PDF indir">
                        <FileText size={22} />
                    </a>
                    {#if record.edit_url}
                        <a href={record.edit_url} class="icon" aria-label="Kaydı düzenle" title="Kaydı düzenle">
                            <Pencil size={22} />
                        </a>
                    {/if}
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table is-fullwidth">
                <tbody>
                    <tr>
                        <th>Kayıt No</th>
                        <td>{record.id}</td>
                    </tr>
                    <tr>
                        <th>Yerleşim Bilgileri</th>
                        <td>{bina.name}<br>{bina.address}</td>
                    </tr>
                    {#if record.type !== 'gider' && record.resident}
                        <tr>
                            <th>Yerleşen Bilgileri</th>
                            <td>{record.resident.name}<br>[ Kapı No {record.resident.door_no} ]</td>
                        </tr>
                    {/if}
                    <tr>
                        <th>Açıklama</th>
                        <td>{record.description}</td>
                    </tr>
                    <tr>
                        <th>Ait Olduğu Dönem</th>
                        <td>{record.period}</td>
                    </tr>
                    <tr>
                        <th>Tutar</th>
                        <td>{record.amount} {bina.pbirimi}</td>
                    </tr>
                    <tr>
                        <th>Durum</th>
                        <td>{record.type}</td>
                    </tr>
                    {#if record.breakdown}
                        <tr>
                            <th>Döküm</th>
                            <td>
                                {#if breakdown.son_okuma !== undefined}
                                    <p>Son Okuma : {breakdown.son_okuma} {breakdown.unit}</p>
                                    <p>İlk Okuma : {breakdown.ilk_okuma} {breakdown.unit}</p>
                                    <p>Birim Bedel : {breakdown.birim_bedel} TL/{breakdown.unit}</p>
                                {/if}
                                {#if breakdown['Apartman Aidat'] !== undefined}
                                    <p>Apartman Aidat : {breakdown['Apartman Aidat']}</p>
                                {/if}
                            </td>
                        </tr>
                    {/if}
                    <tr>
                        <th>Diğer Bilgiler</th>
                        <td>{@html record.remarks ?? ''}</td>
                    </tr>
                    <tr>
                        <th>Dosyalar</th>
                        <td class="has-text-left">
                            {#each record.files as file}
                                <a href={`/kayit-dosya-gor/${file.id}`} class="icon-text file-link">
                                    <FileText size={18} />
                                    <span>{file.name}</span>
                                </a>
                            {/each}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</Layout>

<style>
    .record-actions {
        align-self: center;
    }

    .file-link {
        margin-right: 1rem;
    }
</style>
