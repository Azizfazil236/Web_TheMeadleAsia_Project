<?php
session_start(); // Oturumu başlat

// db.php dosyasını dahil et (veritabanı bağlantısı için)
require 'db.php';

// Kullanıcı girişi kontrolü: Sadece adminler erişebilir
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php"); // Admin değilse giriş sayfasına yönlendir
    exit;
}

$error_message = ""; // Hata mesajları için değişken
$success_message = ""; // Başarı mesajları için değişken

// Ekleme işlemi
if (isset($_POST['ekle'])) {
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $yas = $_POST['yas'];
    $cinsiyet = $_POST['cinsiyet'];
    $telefon = trim($_POST['telefon']);

    // Basit doğrulama
    if (empty($ad) || empty($soyad) || empty($yas) || empty($cinsiyet)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } elseif (!is_numeric($yas) || $yas <= 0) {
        $error_message = "Yaş geçerli bir sayı olmalıdır.";
    } else {
        try {
            // Sadece formda olan alanlar ekleniyor
            $stmt = $db->prepare("INSERT INTO ogrenciler (ad, soyad, yas, cinsiyet, telefon, durum, kayit_tarihi) VALUES (?, ?, ?, ?, ?, 'Aktif', CURDATE())");
            $stmt->execute([$ad, $soyad, $yas, $cinsiyet, $telefon]);
            $success_message = "Öğrenci başarıyla eklendi!";
            // header("Location: ogrenciler.php"); // Mesajı göstermek için yönlendirmeyi kaldır
            // exit;
        } catch (PDOException $e) {
            $error_message = "Öğrenci eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Silme işlemi
if (isset($_GET['sil'])) {
    $id = $_GET['sil'];
    try {
        $stmt = $db->prepare("DELETE FROM ogrenciler WHERE ogrenci_id = ?");
        $stmt->execute([$id]);
        $success_message = "Öğrenci başarıyla silindi!";
        header("Location: ogrenciler.php"); // Silme sonrası sayfayı yenile
        exit;
    } catch (PDOException $e) {
        $error_message = "Öğrenci silinirken bir hata oluştu: " . $e->getMessage();
    }
}

// Güncelleme verisi çekme (formu doldurmak için)
$guncelle = null;
if (isset($_GET['duzenle'])) {
    $id = $_GET['duzenle'];
    try {
        $stmt = $db->prepare("SELECT * FROM ogrenciler WHERE ogrenci_id = ?");
        $stmt->execute([$id]);
        $guncelle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$guncelle) {
            $error_message = "Düzenlenecek öğrenci bulunamadı.";
        }
    } catch (PDOException $e) {
        $error_message = "Öğrenci bilgileri çekilirken bir hata oluştu: " . $e->getMessage();
    }
}

// Güncelleme işlemi
if (isset($_POST['guncelle'])) {
    $id = $_POST['id'];
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $yas = $_POST['yas'];
    $cinsiyet = $_POST['cinsiyet'];
    $telefon = trim($_POST['telefon']);
    $durum = $_POST['durum']; // Durumu da güncelleyebilmek için ekledik

    // Basit doğrulama
    if (empty($ad) || empty($soyad) || empty($yas) || empty($cinsiyet) || empty($id)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } elseif (!is_numeric($yas) || $yas <= 0) {
        $error_message = "Yaş geçerli bir sayı olmalıdır.";
    } else {
        try {
            // Sadece formda olan alanlar güncelleniyor
            $stmt = $db->prepare("UPDATE ogrenciler SET ad=?, soyad=?, yas=?, cinsiyet=?, telefon=?, durum=? WHERE ogrenci_id=?");
            $stmt->execute([$ad, $soyad, $yas, $cinsiyet, $telefon, $durum, $id]);
            $success_message = "Öğrenci bilgileri başarıyla güncellendi!";
            // header("Location: ogrenciler.php"); // Mesajı göstermek için yönlendirmeyi kaldır
            // exit;
        } catch (PDOException $e) {
            $error_message = "Öğrenci güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Listeleme (Her zaman çalışır)
try {
    // Sadece formda olan ve tabloda gösterilen alanlar seçiliyor
    $ogrenciler = $db->query("SELECT ogrenci_id, ad, soyad, yas, cinsiyet, telefon, durum, kayit_tarihi FROM ogrenciler ORDER BY kayit_tarihi DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Öğrenci listesi çekilirken bir hata oluştu: " . $e->getMessage();
    $ogrenciler = []; // Hata durumunda boş liste
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Yönetimi - Bilge Nesil</title>
    <style>
        /* CSS stilleri aynı kalacak, önceki dosyalardaki ile tamamen uyumlu */
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 20px; background: #f0f4f8; color: #1a3c5e; }
        .container { max-width: 900px; margin: 30px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        h2, h3 { color: #007bff; text-align: center; margin-bottom: 25px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #e0e6ed; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9fbfd; }
        tr:hover { background: #eef7ff; }
        a { color: #007bff; text-decoration: none; transition: color 0.3s ease; }
        a:hover { text-decoration: underline; color: #0056b3; }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 15px;
            transition: background 0.3s ease;
            margin-right: 5px;
        }
        .btn:hover { background: #0056b3; }
        .btn-red { background: #dc3545; }
        .btn-red:hover { background: #c82333; }
        .form-section { margin-top: 30px; background: #f9fbfd; padding: 25px; border-radius: 10px; border: 1px solid #e0e6ed; }
        .form-section label { display: block; margin-bottom: 8px; font-weight: bold; color: #1a3c5e; }
        .form-section input[type="text"],
        .form-section input[type="number"],
        .form-section select {
            width: calc(100% - 24px); /* Padding'i hesaba kat */
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box; /* Padding'i genişliğe dahil et */
        }
        .form-section input[type="submit"] {
            width: auto; /* Otomatik genişlik */
            padding: 12px 25px;
            background: linear-gradient(90deg, #28a745, #218838); /* Yeşil buton */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 17px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 10px;
        }
        .form-section input[type="submit"]:hover { background: linear-gradient(90deg, #218838, #1e7e34); }
        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
        }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .back-link { display: block; text-align: center; margin-top: 20px; font-size: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <p class="back-link"><a href="admin.php" class="btn">Admin Paneline Geri Dön</a></p>

        <h2>Öğrenci Yönetimi</h2>

        <?php if (!empty($error_message)): ?>
            <div class="message-box error-message"><?= $error_message ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="message-box success-message"><?= $success_message ?></div>
        <?php endif; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Ad</th>
                <th>Soyad</th>
                <th>Yaş</th>
                <th>Cinsiyet</th>
                <th>Telefon</th>
                <th>Durum</th>
                <th>Kayıt Tarihi</th>
                <th>İşlem</th>
            </tr>
            <?php if (!empty($ogrenciler)): ?>
                <?php foreach ($ogrenciler as $o): ?>
                <tr>
                    <td><?= htmlspecialchars($o['ogrenci_id']) ?></td>
                    <td><?= htmlspecialchars($o['ad']) ?></td>
                    <td><?= htmlspecialchars($o['soyad']) ?></td>
                    <td><?= htmlspecialchars($o['yas']) ?></td>
                    <td><?= htmlspecialchars($o['cinsiyet']) ?></td>
                    <td><?= htmlspecialchars($o['telefon']) ?></td>
                    <td><?= htmlspecialchars($o['durum']) ?></td>
                    <td><?= htmlspecialchars($o['kayit_tarihi']) ?></td>
                    <td>
                        <a href="?duzenle=<?= htmlspecialchars($o['ogrenci_id']) ?>" class="btn">Düzenle</a>
                        <a href="?sil=<?= htmlspecialchars($o['ogrenci_id']) ?>" class="btn btn-red" onclick="return confirm('Silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center;">Henüz kayıtlı öğrenci bulunmamaktadır.</td>
                </tr>
            <?php endif; ?>
        </table>

        <div class="form-section">
            <h3><?= $guncelle ? 'Öğrenci Bilgilerini Güncelle' : 'Yeni Öğrenci Ekle' ?></h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($guncelle['ogrenci_id'] ?? '') ?>">

                <label for="ad">Ad:</label>
                <input type="text" id="ad" name="ad" value="<?= htmlspecialchars($guncelle['ad'] ?? '') ?>" required><br>

                <label for="soyad">Soyad:</label>
                <input type="text" id="soyad" name="soyad" value="<?= htmlspecialchars($guncelle['soyad'] ?? '') ?>" required><br>

                <label for="yas">Yaş:</label>
                <input type="number" id="yas" name="yas" value="<?= htmlspecialchars($guncelle['yas'] ?? '') ?>" required><br>

                <label for="cinsiyet">Cinsiyet:</label>
                <select id="cinsiyet" name="cinsiyet" required>
                    <option value="">Seçiniz</option>
                    <option value="Erkek" <?= isset($guncelle) && $guncelle['cinsiyet'] == 'Erkek' ? 'selected' : '' ?>>Erkek</option>
                    <option value="Kız" <?= isset($guncelle) && $guncelle['cinsiyet'] == 'Kız' ? 'selected' : '' ?>>Kız</option>
                </select><br>

                <label for="telefon">Telefon:</label>
                <input type="text" id="telefon" name="telefon" value="<?= htmlspecialchars($guncelle['telefon'] ?? '') ?>"><br>

                <?php if ($guncelle): // Güncelleme modundaysa durum seçeneğini göster ?>
                    <label for="durum">Durum:</label>
                    <select id="durum" name="durum" required>
                        <option value="Aktif" <?= isset($guncelle) && $guncelle['durum'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Pasif" <?= isset($guncelle) && $guncelle['durum'] == 'Pasif' ? 'selected' : '' ?>>Pasif</option>
                        <option value="Mezun" <?= isset($guncelle) && $guncelle['durum'] == 'Mezun' ? 'selected' : '' ?>>Mezun</option>
                        <option value="Ayrılmış" <?= isset($guncelle) && $guncelle['durum'] == 'Ayrılmış' ? 'selected' : '' ?>>Ayrılmış</option>
                    </select><br>
                <?php endif; ?>

                <input type="submit" name="<?= $guncelle ? 'guncelle' : 'ekle' ?>" value="<?= $guncelle ? 'Güncelle' : 'Ekle' ?>">
            </form>
        </div>
        <p class="back-link"><a href="admin.php" class="btn">Admin Paneline Geri Dön</a></p>
    </div>
</body>
</html>