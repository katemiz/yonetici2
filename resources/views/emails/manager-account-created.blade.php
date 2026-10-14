<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Yönetici Hesabınız Oluşturuldu</title>
</head>
<body>
    <p>Merhaba {{ $manager->name }},</p>

    <p>Sistemde yeni bir yönetici hesabınız oluşturuldu.</p>

    <p>
        <strong>Ad Soyad:</strong> {{ $manager->name }} {{ $manager->lastname }}<br>
        <strong>E-posta:</strong> {{ $manager->email }}<br>
        <strong>Bina Kotası:</strong> {{ $manager->building_quota }}
    </p>

    <p>Hesabınız için bir parola belirlenmemiştir. Aşağıdaki bağlantıdan parolanızı oluşturabilirsiniz:</p>

    <p>
        <a href="{{ route('password.reset', ['token' => $resetToken]) }}">Parola Oluştur</a>
    </p>

    <p>Bu bağlantı kısa süre sonra geçersiz olur.</p>

    <p>Saygılarımızla,<br>{{ config('app.name') }}</p>
</body>
</html>
