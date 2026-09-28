<script>
    import Layout from './Shared/Layout.svelte';
</script>

<svelte:head>
    <title>Aklınızda Bulunsun - eYönetici</title>
</svelte:head>

<Layout>
    <section class="section container">
        <h1 class="title my-6 has-text-weight-light is-size-1 has-text-left">Aklınızda Bulunsun</h1>
        <h2 class="subtitle has-text-weight-light">Yapılması Gerekenler</h2>

        <div class="columns">
            <div class="column is-4">
                <p class="subtitle has-text-info">Bina Tanımı Oluşturun</p>
                <p>
                    Giriş yaptıktan sonra, isminizin altındakı "<strong>Binalarım</strong>" sekmesini
                    kullanarak bina tanımı oluşturabilir ve değişiklikleri yapabilirsiniz
                </p>
            </div>

            <div class="column">
                <figure class="image">
                    <img src="/images/help/h8.png" alt="Yöneteceğiniz binayı oluşturun">
                    <figcaption class="has-text-centered">Şekil 1 Yöneteceğiniz Binayı Oluşturun</figcaption>
                </figure>
            </div>
        </div>

        <div class="columns">
            <div class="column is-4">
                <p class="subtitle has-text-info">Bina Ayarlarını Tanımlayın</p>
                <p>Tüm bedel hesaplamaları burada tanımlayacağınız değerler kullanılarak yapılacaktır.</p>
            </div>

            <div class="column">
                <figure class="image">
                    <img src="/images/help/h2.png" alt="Bina tanımı ve menü">
                    <figcaption class="has-text-centered">Şekil 2 Bina Tanımı ve Menu</figcaption>
                </figure>

                <figure class="image">
                    <img src="/images/help/h4.png" alt="Bina ayarları">
                    <figcaption class="has-text-centered">Şekil 2 Ayarlar</figcaption>
                </figure>
            </div>
        </div>
    </section>
</Layout>
