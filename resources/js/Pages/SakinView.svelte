<script>
    import { ArrowLeft, Pencil } from "@lucide/svelte";
    import Layout from "./Shared/Layout.svelte";

    let { bina, sakin, durum } = $props();
</script>

<svelte:head>
    <title>{sakin.name} {sakin.lastname} - {bina.name}</title>
</svelte:head>

<Layout>
    <main class="section container">
        <h1 class="title has-text-weight-light">Bina Sakini</h1>
        <h2 class="subtitle">Bilgiler</h2>

        <div class="mb-5">
            <a href={`/sakin-list/${bina.id}`} class="icon-text">
                <span class="icon"><ArrowLeft size={18} /></span>
                <span>Geri</span>
            </a>
        </div>

        <article class="card mb-4">
            <div class="card-content">
                <div class="media">
                    <div class="media-content">
                        <p class="title is-4">{sakin.name} {sakin.lastname}</p>

                        <div class="table-container">
                            <table class="table is-fullwidth">
                                <tbody>
                                    <tr>
                                        <td class="has-text-grey has-text-right"
                                            >Kapı Numarası</td
                                        >
                                        <td>{sakin.door_no}</td>
                                    </tr>
                                    <tr>
                                        <td class="has-text-grey has-text-right"
                                            >Ev Sahipliği Durumu</td
                                        >
                                        <td
                                            >{sakin.is_evsahibi
                                                ? "Ev Sahibi"
                                                : "Kiracı"}</td
                                        >
                                    </tr>
                                    <tr>
                                        <td class="has-text-grey has-text-right"
                                            >Telefon</td
                                        >
                                        <td>{sakin.phone}</td>
                                    </tr>
                                    <tr>
                                        <td class="has-text-grey has-text-right"
                                            >E-Posta</td
                                        >
                                        <td>{sakin.email}</td>
                                    </tr>
                                    <tr>
                                        <td class="has-text-grey has-text-right"
                                            >Giriş Tarihi</td
                                        >
                                        <td>{sakin.giris_tarihi}</td>
                                    </tr>
                                    <tr>
                                        <td class="has-text-grey has-text-right"
                                            >Ödeme Oranı</td
                                        >
                                        <td>% {sakin.payratio}</td>
                                    </tr>
                                    <tr>
                                        <td class="has-text-grey has-text-right"
                                            >Notlar/Diğer Bilgiler</td
                                        >
                                        <td>{@html sakin.remarks ?? ""}</td>
                                    </tr>
                                    <tr>
                                        <td class="has-text-grey has-text-right"
                                            >Durum</td
                                        >
                                        <td
                                            >{sakin.is_active
                                                ? durum["1"]
                                                : durum["0"]}</td
                                        >
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="card-footer">
                <a
                    href={`/sakin-form/${bina.id}/${sakin.id}`}
                    class="card-footer-item"
                >
                    <Pencil size={18} />&nbsp;Değiştir
                </a>
            </footer>
        </article>

        <nav class="level">
            <div class="level-left">{sakin.created_at}</div>
            <div class="level-right">{sakin.updated_human}</div>
        </nav>
    </main>
</Layout>
