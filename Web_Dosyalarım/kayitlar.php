<?php
session_start(); // Oturumu başlat

// db.php dosyasını dahil et
require 'db.php';

// Kullanıcı girişi kontrolü: Sadece adminler erişebilir
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php"); // Admin değilse giriş sayfasına yönlendir
    exit;
}

$error_message = ""; // Hata mesajları için değişken
$success_message = ""; // Başarı mesajları için değişken

// --- Veri Çekme Fonksiyonları ---
// Öğrencileri ve kursları dropdown'lar için çekiyoruz
function getStudents($db) {
    try {
        $stmt = $db->query("SELECT ogrenci_id, ad, soyad FROM ogrenciler ORDER BY ad, soyad");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Hata durumunda loglama yapabiliriz
        error_log("Öğrenciler çekilirken hata: " . $e->getMessage());
        return [];
    }
}

function getCourses($db) {
    try {
        $stmt = $db->query("SELECT kurs_id, kurs_kodu, kurs_adi FROM kurslar ORDER BY kurs_adi");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Kurslar çekilirken hata: " . $e->getMessage());
        return [];
    }
}

$ogrenciler = getStudents($db);
$kurslar = getCourses($db);

// Kayıt Durumları (ENUM'dan alınabilir veya manuel tanımlanabilir)
$kayit_durumlari = ['Beklemede', 'Onaylandı', 'Reddedildi', 'Tamamlandı'];

// --- CRUD İşlemleri ---

// Ekleme işlemi
if (isset($_POST['ekle'])) {
    $ogrenci_id = $_POST['ogrenci_id'];
    $kurs_id = $_POST['kurs_id'];
    $kayit_tarihi = $_POST['kayit_tarihi'];
    $durum = $_POST['durum'];

    if (empty($ogrenci_id) || empty($kurs_id) || empty($kayit_tarihi) || empty($durum)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } else {
        try {
            // Kontrol: Bu öğrenci-kurs ikilisi zaten var mı? (Unique constraint olmasa bile mantıksal kontrol)
            $stmt = $db->prepare("SELECT COUNT(*) FROM kayitlar WHERE ogrenci_id = ? AND kurs_id = ?");
            $stmt->execute([$ogrenci_id, $kurs_id]);
            if ($stmt->fetchColumn() > 0) {
                $error_message = "Bu öğrenci zaten seçilen kursa kayıtlı.";
            } else {
                $stmt = $db->prepare("INSERT INTO kayitlar (ogrenci_id, kurs_id, kayit_tarihi, durum) VALUES (?, ?, ?, ?)");
                $stmt->execute([$ogrenci_id, $kurs_id, $kayit_tarihi, $durum]);
                $success_message = "Kayıt başarıyla eklendi!";
                // Ekleme sonrası formu temizlemek için POST'u sıfırlayabiliriz
                $_POST = array(); // Form değerlerini temizler
                header("Location: kayitlar.php?success=" . urlencode($success_message)); // Refresh ile mesaj göster
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Kayıt eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Silme işlemi
if (isset($_GET['sil'])) {
    $id = $_GET['sil'];
    try {
        $stmt = $db->prepare("DELETE FROM kayitlar WHERE kayit_id = ?");
        $stmt->execute([$id]);
        $success_message = "Kayıt başarıyla silindi!";
        header("Location: kayitlar.php?success=" . urlencode($success_message)); // Silme sonrası sayfayı yenile
        exit;
    } catch (PDOException $e) {
        $error_message = "Kayıt silinirken bir hata oluştu: " . $e->getMessage();
    }
}

// Güncelleme verisi çekme (formu doldurmak için)
$guncelle = null;
if (isset($_GET['duzenle'])) {
    $id = $_GET['duzenle'];
    try {
        $stmt = $db->prepare("SELECT kayit_id, ogrenci_id, kurs_id, kayit_tarihi, durum FROM kayitlar WHERE kayit_id = ?");
        $stmt->execute([$id]);
        $guncelle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$guncelle) {
            $error_message = "Düzenlenecek kayıt bulunamadı.";
        }
    } catch (PDOException $e) {
        $error_message = "Kayıt bilgileri çekilirken bir hata oluştu: " . $e->getMessage();
    }
}

// Güncelleme işlemi
if (isset($_POST['guncelle'])) {
    $id = $_POST['id'];
    $ogrenci_id = $_POST['ogrenci_id'];
    $kurs_id = $_POST['kurs_id'];
    $kayit_tarihi = $_POST['kayit_tarihi'];
    $durum = $_POST['durum'];

    if (empty($id) || empty($ogrenci_id) || empty($kurs_id) || empty($kayit_tarihi) || empty($durum)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } else {
        try {
            // Güncellenen öğrenci-kurs ikilisinin başka bir kayıtla çakışmadığını kontrol et
            $stmt = $db->prepare("SELECT COUNT(*) FROM kayitlar WHERE ogrenci_id = ? AND kurs_id = ? AND kayit_id != ?");
            $stmt->execute([$ogrenci_id, $kurs_id, $id]);
            if ($stmt->fetchColumn() > 0) {
                $error_message = "Bu öğrenci seçilen kursa başka bir kayıtla zaten bağlı. Lütfen farklı bir kurs veya öğrenci seçin.";
            } else {
                $stmt = $db->prepare("UPDATE kayitlar SET ogrenci_id=?, kurs_id=?, kayit_tarihi=?, durum=? WHERE kayit_id=?");
                $stmt->execute([$ogrenci_id, $kurs_id, $kayit_tarihi, $durum, $id]);
                $success_message = "Kayıt bilgileri başarıyla güncellendi!";
                header("Location: kayitlar.php?success=" . urlencode($success_message)); // Güncelleme sonrası refresh
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Kayıt güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Mesajları GET parametresinden al
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}


// Listeleme (Her zaman çalışır)
try {
    $kayitlar = $db->query("
        SELECT kay.kayit_id, kay.kayit_tarihi, kay.durum,
               CONCAT(o.ad, ' ', o.soyad) AS ogrenci_adi,
               k.kurs_adi
        FROM kayitlar kay
        JOIN ogrenciler o ON kay.ogrenci_id = o.ogrenci_id
        JOIN kurslar k ON kay.kurs_id = k.kurs_id
        ORDER BY kay.kayit_tarihi DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = (isset($error_message) && !empty($error_message)) ? $error_message : "Kayıt listesi çekilirken bir hata oluştu: " . $e->getMessage();
    $kayitlar = []; // Hata durumunda boş liste
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Yönetimi - Bilge Nesil</title>
    <style>
        /* CSS stilleri önceki dosyalarınızdaki ile tamamen uyumlu */
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
        .form-section input[type="email"],
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

        <h2>Kayıt Yönetimi</h2>

        <?php if (!empty($error_message)): ?>
            <div class="message-box error-message"><?= $error_message ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="message-box success-message"><?= $success_message ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Öğrenci</th>
                    <th>Kurs</th>
                    <th>Kayıt Tarihi</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($kayitlar)): ?>
                    <?php foreach ($kayitlar as $k): ?>
                    <tr>
                        <td><?= htmlspecialchars($k['kayit_id']) ?></td>
                        <td><?= htmlspecialchars($k['ogrenci_adi']) ?></td>
                        <td><?= htmlspecialchars($k['kurs_adi']) ?></td>
                        <td><?= htmlspecialchars($k['kayit_tarihi']) ?></td>
                        <td><?= htmlspecialchars($k['durum']) ?></td>
                        <td>
                            <a href="?duzenle=<?= htmlspecialchars($k['kayit_id']) ?>" class="btn">Düzenle</a>
                            <a href="?sil=<?= htmlspecialchars($k['kayit_id']) ?>" class="btn btn-red" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')">Sil</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Henüz kayıt bulunmamaktadır.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="form-section">
            <h3><?= $guncelle ? 'Kayıt Bilgilerini Güncelle' : 'Yeni Kayıt Ekle' ?></h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($guncelle['kayit_id'] ?? '') ?>">

                <label for="ogrenci_id">Öğrenci:</label>
                <select id="ogrenci_id" name="ogrenci_id" required>
                    <option value="">Öğrenci Seçiniz</option>
                    <?php foreach ($ogrenciler as $ogrenci): ?>
                        <option value="<?= htmlspecialchars($ogrenci['ogrenci_id']) ?>"
                            <?= isset($guncelle) && $guncelle['ogrenci_id'] == $ogrenci['ogrenci_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ogrenci['ad'] . ' ' . $ogrenci['soyad']) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <label for="kurs_id">Kurs:</label>
                <select id="kurs_id" name="kurs_id" required>
                    <option value="">Kurs Seçiniz</option>
                    <?php foreach ($kurslar as $kurs): ?>
                        <option value="<?= htmlspecialchars($kurs['kurs_id']) ?>"
                            <?= isset($guncelle) && $guncelle['kurs_id'] == $kurs['kurs_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kurs['kurs_adi'] . ' (' . $kurs['kurs_kodu'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <label for="kayit_tarihi">Kayıt Tarihi:</label>
                <input type="date" id="kayit_tarihi" name="kayit_tarihi" value="<?= htmlspecialchars($guncelle['kayit_tarihi'] ?? date('Y-m-d')) ?>" required><br>

                <label for="durum">Durum:</label>
                <select id="durum" name="durum" required>
                    <?php foreach ($kayit_durumlari as $durum_val): ?>
                        <option value="<?= htmlspecialchars($durum_val) ?>"
                            <?= isset($guncelle) && $guncelle['durum'] == $durum_val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($durum_val) ?>
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