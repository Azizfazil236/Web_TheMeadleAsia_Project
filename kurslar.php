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
    $kurs_adi = trim($_POST['kurs_adi']);
    $seviye = $_POST['seviye'];
    $toplam_ders_saati = $_POST['toplam_ders_saati'];
    // Yeni eklenen alan: haftalik_ders_saati
    $haftalik_ders_saati = $_POST['haftalik_ders_saati']; 
    $sorumlu_egitmen_id = $_POST['sorumlu_egitmen_id'];

    // Basit doğrulama (haftalik_ders_saati de eklendi)
    if (empty($kurs_adi) || empty($seviye) || empty($toplam_ders_saati) || empty($haftalik_ders_saati) || empty($sorumlu_egitmen_id)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } elseif (!is_numeric($toplam_ders_saati) || $toplam_ders_saati <= 0) {
        $error_message = "Toplam ders saati geçerli bir sayı olmalıdır.";
    } elseif (!is_numeric($haftalik_ders_saati) || $haftalik_ders_saati <= 0) {
        $error_message = "Haftalık ders saati geçerli bir sayı olmalıdır.";
    } else {
        try {
            // SQL INSERT sorgusu güncellendi (haftalik_ders_saati eklendi)
            $stmt = $db->prepare("INSERT INTO kurslar (kurs_adi, kurs_kodu, seviye, toplam_ders_saati, haftalik_ders_saati, sorumlu_egitmen_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$kurs_adi, uniqid('K-'), $seviye, $toplam_ders_saati, $haftalik_ders_saati, $sorumlu_egitmen_id]);
            $success_message = "Kurs başarıyla eklendi!";
            //header("Location: kurslar.php"); // Mesajı göstermek için yönlendirmeyi kaldır
            //exit;
        } catch (PDOException $e) {
            $error_message = "Kurs eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Silme işlemi
if (isset($_GET['sil'])) {
    $id = $_GET['sil'];
    try {
        $stmt = $db->prepare("DELETE FROM kurslar WHERE kurs_id = ?");
        $stmt->execute([$id]);
        $success_message = "Kurs başarıyla silindi!";
        header("Location: kurslar.php"); // Silme sonrası sayfayı yenile
        exit;
    } catch (PDOException $e) {
        $error_message = "Kurs silinirken bir hata oluştu: " . $e->getMessage();
    }
}

// Güncelleme verisi çekme (formu doldurmak için)
$guncelle = null;
if (isset($_GET['duzenle'])) {
    $id = $_GET['duzenle'];
    try {
        $stmt = $db->prepare("SELECT * FROM kurslar WHERE kurs_id = ?");
        $stmt->execute([$id]);
        $guncelle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$guncelle) {
            $error_message = "Düzenlenecek kurs bulunamadı.";
        }
    } catch (PDOException $e) {
        $error_message = "Kurs bilgileri çekilirken bir hata oluştu: " . $e->getMessage();
    }
}

// Güncelleme işlemi
if (isset($_POST['guncelle'])) {
    $id = $_POST['id'];
    $kurs_adi = trim($_POST['kurs_adi']);
    $seviye = $_POST['seviye'];
    $toplam_ders_saati = $_POST['toplam_ders_saati'];
    // Yeni eklenen alan: haftalik_ders_saati
    $haftalik_ders_saati = $_POST['haftalik_ders_saati']; 
    $sorumlu_egitmen_id = $_POST['sorumlu_egitmen_id'];

    // Basit doğrulama (haftalik_ders_saati de eklendi)
    if (empty($kurs_adi) || empty($seviye) || empty($toplam_ders_saati) || empty($haftalik_ders_saati) || empty($sorumlu_egitmen_id) || empty($id)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } elseif (!is_numeric($toplam_ders_saati) || $toplam_ders_saati <= 0) {
        $error_message = "Toplam ders saati geçerli bir sayı olmalıdır.";
    } elseif (!is_numeric($haftalik_ders_saati) || $haftalik_ders_saati <= 0) {
        $error_message = "Haftalık ders saati geçerli bir sayı olmalıdır.";
    } else {
        try {
            // SQL UPDATE sorgusu güncellendi (haftalik_ders_saati eklendi)
            $stmt = $db->prepare("UPDATE kurslar SET kurs_adi=?, seviye=?, toplam_ders_saati=?, haftalik_ders_saati=?, sorumlu_egitmen_id=? WHERE kurs_id=?");
            $stmt->execute([$kurs_adi, $seviye, $toplam_ders_saati, $haftalik_ders_saati, $sorumlu_egitmen_id, $id]);
            $success_message = "Kurs bilgileri başarıyla güncellendi!";
            //header("Location: kurslar.php"); // Mesajı göstermek için yönlendirmeyi kaldır
            //exit;
        } catch (PDOException $e) {
            $error_message = "Kurs güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Listeleme (Her zaman çalışır)
try {
    // Listeleme sorgusu güncellendi (haftalik_ders_saati eklendi)
    $kurslar = $db->query("SELECT k.*, CONCAT(e.ad, ' ', e.soyad) AS egitmen_ad
                            FROM kurslar k
                            LEFT JOIN egitmenler e ON k.sorumlu_egitmen_id = e.egitmen_id
                            ORDER BY k.kurs_id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Kurs listesi çekilirken bir hata oluştu: " . $e->getMessage();
    $kurslar = []; // Hata durumunda boş liste
}

// Eğitmen listesi
try {
    $egitmenler = $db->query("SELECT egitmen_id, CONCAT(ad, ' ', soyad) AS adsoyad FROM egitmenler ORDER BY adsoyad ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Eğitmen listesi çekilirken bir hata oluştu: " . $e->getMessage();
    $egitmenler = []; // Hata durumunda boş liste
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs Yönetimi - Bilge Nesil</title>
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

        <h2>Kurs Yönetimi</h2>

        <?php if (!empty($error_message)): ?>
            <div class="message-box error-message"><?= $error_message ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="message-box success-message"><?= $success_message ?></div>
        <?php endif; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Kurs Adı</th>
                <th>Kurs Kodu</th>
                <th>Seviye</th>
                <th>Toplam Ders Saati</th>
                <th>Haftalık Ders Saati</th> <th>Eğitmen</th>
                <th>İşlem</th>
            </tr>
            <?php if (!empty($kurslar)): ?>
                <?php foreach ($kurslar as $k): ?>
                <tr>
                    <td><?= htmlspecialchars($k['kurs_id']) ?></td>
                    <td><?= htmlspecialchars($k['kurs_adi']) ?></td>
                    <td><?= htmlspecialchars($k['kurs_kodu']) ?></td>
                    <td><?= htmlspecialchars($k['seviye']) ?></td>
                    <td><?= htmlspecialchars($k['toplam_ders_saati']) ?></td>
                    <td><?= htmlspecialchars($k['haftalik_ders_saati']) ?></td> <td><?= htmlspecialchars($k['egitmen_ad']) ?></td>
                    <td>
                        <a href="?duzenle=<?= htmlspecialchars($k['kurs_id']) ?>" class="btn">Düzenle</a>
                        <a href="?sil=<?= htmlspecialchars($k['kurs_id']) ?>" class="btn btn-red" onclick="return confirm('Silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">Henüz kayıtlı kurs bulunmamaktadır.</td> </tr>
            <?php endif; ?>
        </table>

        <div class="form-section">
            <h3><?= $guncelle ? 'Kurs Bilgilerini Güncelle' : 'Yeni Kurs Ekle' ?></h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($guncelle['kurs_id'] ?? '') ?>">
                
                <label for="kurs_adi">Kurs Adı:</label>
                <input type="text" id="kurs_adi" name="kurs_adi" value="<?= htmlspecialchars($guncelle['kurs_adi'] ?? '') ?>" required><br>
                
                <label for="seviye">Seviye:</label>
                <select id="seviye" name="seviye" required>
                    <option value="">Seçiniz</option>
                    <option value="Başlangıç" <?= isset($guncelle) && $guncelle['seviye'] == 'Başlangıç' ? 'selected' : '' ?>>Başlangıç</option>
                    <option value="Orta" <?= isset($guncelle) && $guncelle['seviye'] == 'Orta' ? 'selected' : '' ?>>Orta</option>
                    <option value="İleri" <?= isset($guncelle) && $guncelle['seviye'] == 'İleri' ? 'selected' : '' ?>>İleri</option>
                </select><br>
                
                <label for="toplam_ders_saati">Toplam Ders Saati:</label>
                <input type="number" id="toplam_ders_saati" name="toplam_ders_saati" value="<?= htmlspecialchars($guncelle['toplam_ders_saati'] ?? '') ?>" required><br>
                
                <label for="haftalik_ders_saati">Haftalık Ders Saati:</label>
                <input type="number" id="haftalik_ders_saati" name="haftalik_ders_saati" value="<?= htmlspecialchars($guncelle['haftalik_ders_saati'] ?? '') ?>" required><br>
                
                <label for="sorumlu_egitmen_id">Eğitmen:</label>
                <select id="sorumlu_egitmen_id" name="sorumlu_egitmen_id" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($egitmenler as $e): ?>
                        <option value="<?= htmlspecialchars($e['egitmen_id']) ?>" <?= isset($guncelle) && $guncelle['sorumlu_egitmen_id'] == $e['egitmen_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['adsoyad']) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <input type="submit" name="<?= $guncelle ? 'guncelle' : 'ekle' ?>" value="<?= $guncelle ? 'Güncelle' : 'Ekle' ?>">
            </form>
        </div>
        <p class="back-link"><a href="admin.php" class="btn">Admin Paneline Geri Dön</a></p>
    </div>
</body>
</html>