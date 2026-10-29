<script>
    import { ArrowLeft, Pencil } from "@lucide/svelte";
    import Layout from "./Shared/Layout.svelte";

    let { bina, sakin, durum } = $props();
</script>

<svelte:head>
    <title>{sakin.name} {sakin.lastname} - {bina.name}</title>
</svelte:head>

<Layout>
    <div>
        <!-- Receipt Main Card -->
        <div class="receipt-card m-6">
            <!-- Header -->
            <div class="receipt-header">
                <div class="columns is-vcentered is-mobile">
                    <div class="column is-7-tablet is-7-mobile">
                        <div class="building-brand">
                            <div class="building-icon">
                                <i class="fa-solid fa-house-chimney"></i>
                            </div>
                            <div>
                                <h1 class="building-title">
                                    Pembeköşk Apartmanı
                                </h1>
                                <p class="building-subtitle">
                                    Beste Sokak 2 Aşağı Eğlence, Etlik
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="column is-5-tablet is-5-mobile">
                        <h2 class="receipt-main-title">GELİR MAKBUZU</h2>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="receipt-body">
                <div class="columns is-variable is-2">
                    <!-- Left: Alındı Dökümü -->
                    <div class="column is-5">
                        <div class="dokum-table-container">
                            <div>
                                <table class="table dokum-table is-bordered">
                                    <thead>
                                        <tr>
                                            <th colspan="2">ALINDI DÖKÜMÜ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Apartman Aidat</td>
                                            <td class="amount-cell">4 000,00</td
                                            >
                                        </tr>
                                        <tr>
                                            <td>Ekim 2026 dönemi aidatı</td>
                                            <td class="amount-cell">4 000,00</td
                                            >
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="dokum-spacer"></div>

                            <div class="dokum-footer">
                                <div class="dokum-footer-label">TOPLAM</div>
                                <div class="dokum-footer-value">4 000,00</div>
                            </div>
                        </div>
                    </div>

                    <!-- Middle: QR Code -->
                    <div
                        class="column is-2-desktop is-2-tablet has-text-centered"
                    >
                        <div class="qr-section">
                            <div class="qr-code-box">
                                <!-- Inline SVG QR Code representation matching receipt -->
                                <svg
                                    viewBox="0 0 100 100"
                                    width="100%"
                                    height="100%"
                                    shape-rendering="crispEdges"
                                >
                                    <rect
                                        width="100"
                                        height="100"
                                        fill="white"
                                    />
                                    <!-- QR Outer Corners -->
                                    <path
                                        d="M0,0 h35 v35 h-35 z M5,5 v25 h25 v-25 z M10,10 h15 v15 h-15 z"
                                        fill="black"
                                    />
                                    <path
                                        d="M65,0 h35 v35 h-35 z M70,5 v25 h25 v-25 z M75,10 h15 v15 h-15 z"
                                        fill="black"
                                    />
                                    <path
                                        d="M0,65 h35 v35 h-35 z M5,70 v25 h25 v-25 z M10,75 h15 v15 h-15 z"
                                        fill="black"
                                    />
                                    <!-- QR Synthetic Data Pixels -->
                                    <rect
                                        x="40"
                                        y="5"
                                        width="8"
                                        height="8"
                                        fill="black"
                                    />
                                    <rect
                                        x="52"
                                        y="12"
                                        width="8"
                                        height="8"
                                        fill="black"
                                    />
                                    <rect
                                        x="42"
                                        y="24"
                                        width="12"
                                        height="6"
                                        fill="black"
                                    />
                                    <rect
                                        x="8"
                                        y="42"
                                        width="10"
                                        height="10"
                                        fill="black"
                                    />
                                    <rect
                                        x="24"
                                        y="45"
                                        width="12"
                                        height="8"
                                        fill="black"
                                    />
                                    <rect
                                        x="42"
                                        y="40"
                                        width="16"
                                        height="16"
                                        fill="black"
                                    />
                                    <rect
                                        x="62"
                                        y="42"
                                        width="8"
                                        height="8"
                                        fill="black"
                                    />
                                    <rect
                                        x="75"
                                        y="42"
                                        width="18"
                                        height="8"
                                        fill="black"
                                    />
                                    <rect
                                        x="85"
                                        y="55"
                                        width="8"
                                        height="12"
                                        fill="black"
                                    />
                                    <rect
                                        x="42"
                                        y="62"
                                        width="8"
                                        height="18"
                                        fill="black"
                                    />
                                    <rect
                                        x="55"
                                        y="65"
                                        width="12"
                                        height="8"
                                        fill="black"
                                    />
                                    <rect
                                        x="70"
                                        y="65"
                                        width="8"
                                        height="25"
                                        fill="black"
                                    />
                                    <rect
                                        x="82"
                                        y="72"
                                        width="12"
                                        height="8"
                                        fill="black"
                                    />
                                    <rect
                                        x="42"
                                        y="85"
                                        width="22"
                                        height="8"
                                        fill="black"
                                    />
                                    <rect
                                        x="82"
                                        y="85"
                                        width="8"
                                        height="10"
                                        fill="black"
                                    />
                                </svg>
                            </div>
                            <span class="belge-no">Belge No 564</span>
                        </div>
                    </div>

                    <!-- Right: Meta & Values -->
                    <div class="column is-5">
                        <!-- Date -->
                        <div class="date-field">2026-10-27 08:48:12</div>

                        <!-- Details -->
                        <div class="info-field-row">
                            <div class="info-label">Kapı No</div>
                            <div class="info-value is-justify-content-flex-end">
                                2
                            </div>
                        </div>

                        <div class="info-field-row">
                            <div class="info-label">Ad-Soyad</div>
                            <div class="info-value is-justify-content-flex-end">
                                Kılıç Ali Temiz
                            </div>
                        </div>

                        <div class="info-field-row">
                            <div class="info-label">Dönem</div>
                            <div class="info-value">03</div>
                        </div>

                        <!-- Total Numeric Amount -->
                        <div class="total-amount-box">
                            <div class="total-amount-text">4 000,00 TL</div>
                        </div>

                        <!-- Total Text Amount -->
                        <div class="written-amount-box">
                            Yalnız dört bin TL tahsil edilmiştir.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="receipt-footer-signatures">
                <div class="signature-block">
                    <div class="signature-title">
                        Pembeköşk Apartmanı Yöneticisi
                    </div>
                    <div class="signature-name">Kılıç Ali TEMİZ</div>
                </div>
                <div class="signature-block">
                    <div class="signature-title">Mühür ve İmza</div>
                </div>
            </div>
        </div>

        <!-- External Footer Bar -->
        <div class="system-meta-footer">
            <div>Bina Yönetim Sistemi</div>
            <div>http://127.0.0.1:8000</div>
        </div>
    </div>
</Layout>

<style>
    :root {
        --border-navy: #1d3557;
        --bg-light-gray: #f4f6f8;
        --bg-field: #eef2f5;
        --text-dark: #1f2937;
    }

    body {
        background-color: #e9ecef;
        color: var(--text-dark);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
            "Helvetica Neue", Arial, sans-serif;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px 10px;
    }

    /* Control Bar styling */
    .controls-bar {
        width: 100%;
        max-width: 960px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Container Card designed to emulate document paper */
    .receipt-card {
        background-color: #ffffff;
        width: 100%;
        max-width: 960px;
        border: 2px solid var(--border-navy);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        border-radius: 4px;
        overflow: hidden;
        position: relative;
    }

    /* Top Header */
    .receipt-header {
        border-bottom: 2px solid var(--border-navy);
        padding: 18px 24px;
    }

    .building-brand {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .building-icon {
        color: #1a43bf;
        font-size: 3.2rem;
        line-height: 1;
    }

    .building-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2px !important;
    }

    .building-subtitle {
        font-size: 0.95rem;
        color: #4b5563;
    }

    .receipt-main-title {
        font-size: 2.2rem;
        font-weight: 900;
        letter-spacing: 1px;
        color: #000000;
        text-align: right;
        margin: 0;
        text-transform: uppercase;
    }

    /* Body Section Grid */
    .receipt-body {
        padding: 20px 24px;
    }

    /* Left Column: Alındı Dökümü Table */
    .dokum-table-container {
        border: 2px solid #2b4360;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background-color: #ffffff;
    }

    .dokum-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0 !important;
    }

    .dokum-table th {
        background-color: #e5e8ec !important;
        color: #111827 !important;
        text-align: center !important;
        font-size: 1.1rem;
        font-weight: 800;
        border-bottom: 2px solid #2b4360 !important;
        padding: 8px 12px !important;
        letter-spacing: 0.5px;
    }

    .dokum-table td {
        padding: 8px 12px !important;
        font-size: 0.95rem;
        border-color: #2b4360 !important;
        color: #1f2937;
    }

    .dokum-table td.amount-cell {
        text-align: right;
        font-weight: 600;
        width: 110px;
    }

    .dokum-spacer {
        flex-grow: 1;
        min-height: 160px;
        border-right: 1px solid #2b4360;
        border-left: 1px solid #2b4360;
    }

    .dokum-footer {
        border-top: 2px solid #2b4360;
        background-color: #f1f3f6;
        display: flex;
    }

    .dokum-footer-label {
        flex-grow: 1;
        padding: 8px 12px;
        font-weight: 700;
        font-size: 0.9rem;
        color: #1f2937;
        text-transform: uppercase;
    }

    .dokum-footer-value {
        width: 110px;
        padding: 8px 12px;
        font-weight: 700;
        text-align: right;
        border-left: 1px solid #2b4360;
        font-size: 0.95rem;
    }

    /* Middle Column: QR Code */
    .qr-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        padding-top: 5px;
    }

    .qr-code-box {
        width: 120px;
        height: 120px;
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qr-code-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .belge-no {
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        margin-top: 6px;
    }

    /* Right Column: Info Fields */
    .info-field-row {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        gap: 10px;
    }

    .info-label {
        width: 90px;
        background-color: #ebeef2;
        padding: 6px 10px;
        font-weight: 600;
        font-size: 0.95rem;
        text-align: center;
        border-radius: 2px;
        color: #374151;
        flex-shrink: 0;
    }

    .info-value {
        flex-grow: 1;
        background-color: #f1f4f8;
        padding: 6px 14px;
        font-size: 0.95rem;
        font-weight: 600;
        color: #111827;
        border-radius: 2px;
        min-height: 35px;
        display: flex;
        align-items: center;
    }

    .date-field {
        background-color: #f1f4f8;
        padding: 6px 14px;
        font-size: 0.95rem;
        font-weight: 600;
        color: #111827;
        text-align: right;
        width: 100%;
        border-radius: 2px;
        margin-bottom: 12px;
    }

    /* Amount Highlights */
    .total-amount-box {
        background-color: #eeeeee;
        padding: 16px;
        text-align: right;
        border-bottom: 2px solid #2b4360;
        margin-top: 15px;
        margin-bottom: 15px;
        border-radius: 2px;
    }

    .total-amount-text {
        font-size: 2.3rem;
        font-weight: 900;
        color: #000000;
        letter-spacing: 0.5px;
        line-height: 1;
    }

    .written-amount-box {
        background-color: #eeeeee;
        padding: 12px 16px;
        font-size: 1.05rem;
        font-weight: 500;
        color: #1f2937;
        border-radius: 2px;
        min-height: 110px;
    }

    /* Footer / Signatures */
    .receipt-footer-signatures {
        border-top: 2px solid var(--border-navy);
        display: flex;
        background-color: #ffffff;
    }

    .signature-block {
        flex: 1;
        padding: 10px 16px 20px 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 110px;
    }

    .signature-block:first-child {
        border-right: 1px solid #d1d5db;
        background-color: #f8fafc;
    }

    .signature-title {
        font-size: 0.85rem;
        color: #4b5563;
        font-weight: 600;
        margin-bottom: 18px;
        text-align: center;
    }

    .signature-name {
        font-size: 1.4rem;
        font-weight: 900;
        color: #000000;
        margin-top: auto;
        text-align: center;
        letter-spacing: 0.5px;
    }

    /* System Meta Footer */
    .system-meta-footer {
        padding: 6px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.8rem;
        color: #4b5563;
        background-color: #ffffff;
        width: 100%;
        max-width: 960px;
        margin-top: 4px;
    }

    /* PRINT STYLES */
    @media print {
        body {
            background-color: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
        }

        .no-print,
        .controls-bar {
            display: none !important;
        }

        .receipt-card {
            max-width: 100% !important;
            border: 2px solid #000000 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            page-break-inside: avoid;
        }

        .system-meta-footer {
            max-width: 100% !important;
            padding: 4px 10px !important;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }
    }
</style>
