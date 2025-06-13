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

// Güncelleme için veri çekme (formu doldurmak için)
$guncelle = null;
if (isset($_GET['duzenle'])) {
    $id = $_GET['duzenle'];
    try {
        $stmt = $db->prepare("SELECT * FROM devam WHERE devam_id = ?");
        $stmt->execute([$id]);
        $guncelle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$guncelle) {
            $error_message = "Düzenlenecek devam kaydı bulunamadı.";
        }
    } catch (PDOException $e) {
        $error_message = "Devam kaydı bilgileri çekilirken bir hata oluştu: " . $e->getMessage();
    }
}


// Ekleme işlemi
if (isset($_POST['ekle'])) {
    $ogrenci_id = $_POST['ogrenci_id'];
    $kurs_id = $_POST['kurs_id'];
    $tarih = $_POST['tarih'];
    $katilim_durumu = $_POST['katilim_durumu'];

    // Basit doğrulama
    if (empty($ogrenci_id) || empty($kurs_id) || empty($tarih) || empty($katilim_durumu)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO devam (ogrenci_id, kurs_id, tarih, katilim_durumu) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ogrenci_id, $kurs_id, $tarih, $katilim_durumu]);
            $success_message = "Devam kaydı başarıyla eklendi!";
            //header("Location: devam.php"); // Mesajı göstermek için yönlendirmeyi kaldır
            //exit;
        } catch (PDOException $e) {
            $error_message = "Devam kaydı eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Silme işlemi
if (isset($_GET['sil'])) {
    $id = $_GET['sil'];
    try {
        $stmt = $db->prepare("DELETE FROM devam WHERE devam_id = ?");
        $stmt->execute([$id]);
        $success_message = "Devam kaydı başarıyla silindi!";
        header("Location: devam.php"); // Silme sonrası sayfayı yenile
        exit;
    } catch (PDOException $e) {
        $error_message = "Devam kaydı silinirken bir hata oluştu: " . $e->getMessage();
    }
}

// Güncelleme işlemi
if (isset($_POST['guncelle'])) {
    $id = $_POST['id'];
    $ogrenci_id = $_POST['ogrenci_id'];
    $kurs_id = $_POST['kurs_id'];
    $tarih = $_POST['tarih'];
    $katilim_durumu = $_POST['katilim_durumu'];

    // Basit doğrulama
    if (empty($id) || empty($ogrenci_id) || empty($kurs_id) || empty($tarih) || empty($katilim_durumu)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } else {
        try {
            $stmt = $db->prepare("UPDATE devam SET ogrenci_id=?, kurs_id=?, tarih=?, katilim_durumu=? WHERE devam_id=?");
            $stmt->execute([$ogrenci_id, $kurs_id, $tarih, $katilim_durumu, $id]);
            $success_message = "Devam kaydı başarıyla güncellendi!";
            //header("Location: devam.php"); // Mesajı göstermek için yönlendirmeyi kaldır
            //exit;
        } catch (PDOException $e) {
            $error_message = "Devam kaydı güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Listeleme (Her zaman çalışır)
try {
    $devamlar = $db->query("
        SELECT d.devam_id, d.tarih, d.katilim_durumu,
               CONCAT(o.ad, ' ', o.soyad) AS ogrenci_ad,
               k.kurs_adi
        FROM devam d
        JOIN ogrenciler o ON d.ogrenci_id = o.ogrenci_id
        JOIN kurslar k ON d.kurs_id = k.kurs_id
        ORDER BY d.tarih DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Devam listesi çekilirken bir hata oluştu: " . $e->getMessage();
    $devamlar = []; // Hata durumunda boş liste
}

// Öğrenciler ve kurslar listeleri (formlar için)
try {
    $ogrenciler = $db->query("SELECT ogrenci_id, CONCAT(ad, ' ', soyad) AS adsoyad FROM ogrenciler ORDER BY adsoyad ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Öğrenci listesi çekilirken bir hata oluştu: " . $e->getMessage();
    $ogrenciler = [];
}

try {
    $kurslar = $db->query("SELECT kurs_id, kurs_adi FROM kurslar ORDER BY kurs_adi ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Kurs listesi çekilirken bir hata oluştu: " . $e->getMessage();
    $kurslar = [];
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devam Takibi - Bilge Nesil</title>
    <style>
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
        .form-section input[type="date"],
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

        <h2>Devam Takibi</h2>

        <?php if (!empty($error_message)): ?>
            <div class="message-box error-message"><?= $error_message ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="message-box success-message"><?= $success_message ?></div>
        <?php endif; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Tarih</th>
                <th>Öğrenci</th>
                <th>Kurs</th>
                <th>Katılım Durumu</th>
                <th>İşlem</th>
            </tr>
            <?php if (!empty($devamlar)): ?>
                <?php foreach ($devamlar as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['devam_id']) ?></td>
                    <td><?= htmlspecialchars($d['tarih']) ?></td>
                    <td><?= htmlspecialchars($d['ogrenci_ad']) ?></td>
                    <td><?= htmlspecialchars($d['kurs_adi']) ?></td>
                    <td><?= htmlspecialchars($d['katilim_durumu']) ?></td>
                    <td>
                        <a href="?duzenle=<?= htmlspecialchars($d['devam_id']) ?>" class="btn">Düzenle</a>
                        <a href="?sil=<?= htmlspecialchars($d['devam_id']) ?>" class="btn btn-red" onclick="return confirm('Devam kaydını silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Henüz devam kaydı bulunmamaktadır.</td>
                </tr>
            <?php endif; ?>
        </table>

        <div class="form-section">
            <h3><?= $guncelle ? 'Devam Kaydını Güncelle' : 'Yeni Devam Kaydı Ekle' ?></h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($guncelle['devam_id'] ?? '') ?>">
                
                <label for="tarih">Tarih:</label>
                <input type="date" id="tarih" name="tarih" value="<?= htmlspecialchars($guncelle['tarih'] ?? date('Y-m-d')) ?>" required><br>
                
                <label for="ogrenci_id">Öğrenci:</label>
                <select id="ogrenci_id" name="ogrenci_id" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($ogrenciler as $o): ?>
                        <option value="<?= htmlspecialchars($o['ogrenci_id']) ?>" <?= isset($guncelle) && $guncelle['ogrenci_id'] == $o['ogrenci_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($o['adsoyad']) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>
                
                <label for="kurs_id">Kurs:</label>
                <select id="kurs_id" name="kurs_id" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($kurslar as $k): ?>
                        <option value="<?= htmlspecialchars($k['kurs_id']) ?>" <?= isset($guncelle) && $guncelle['kurs_id'] == $k['kurs_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['kurs_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>
                
                <label for="katilim_durumu">Katılım Durumu:</label>
                <select id="katilim_durumu" name="katilim_durumu" required>
                    <option value="">Seçiniz</option>
                    <option value="Katıldı" <?= isset($guncelle) && $guncelle['katilim_durumu'] == 'Katıldı' ? 'selected' : '' ?>>Katıldı</option>
                    <option value="Katılmadı" <?= isset($guncelle) && $guncelle['katilim_durumu'] == 'Katılmadı' ? 'selected' : '' ?>>Katılmadı</option>
                    <option value="Geç Katıldı" <?= isset($guncelle) && $guncelle['katilim_durumu'] == 'Geç Katıldı' ? 'selected' : '' ?>>Geç Katıldı</option>
                    <option value="İzinli" <?= isset($guncelle) && $guncelle['katilim_durumu'] == 'İzinli' ? 'selected' : '' ?>>İzinli</option>
                </select><br>
                
                <input type="submit" name="<?= $guncelle ? 'guncelle' : 'ekle' ?>" value="<?= $guncelle ? 'Güncelle' : 'Ekle' ?>">
            </form>
        </div>
        <p class="back-link"><a href="admin.php" class="btn">Admin Paneline Geri Dön</a></p>
    </div>
</body>
</html>