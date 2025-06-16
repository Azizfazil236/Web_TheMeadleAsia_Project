<?php
session_start();

require 'db.php'; 

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

$error_message = "";   
$success_message = "";  

function getStudents($db) {
    try {
        $stmt = $db->query("SELECT ogrenci_id, ad, soyad FROM ogrenciler ORDER BY ad, soyad");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Öğrenciler çekilirken hata: " . $e->getMessage());
        return [];
    }
}

function getCourses($db) {
    try {
        $stmt = $db->query("SELECT kurs_id, kurs_adi FROM kurslar ORDER BY kurs_adi");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Kurslar çekilirken hata: " . $e->getMessage());
        return [];
    }
}

$ogrenciler = getStudents($db);
$kurslar = getCourses($db);



if (isset($_POST['ekle'])) {
    $ogrenci_id = $_POST['ogrenci_id'];
    $kurs_id = $_POST['kurs_id'];
    $tamamlanma_tarihi = $_POST['tamamlanma_tarihi'];
    $sertifika_no = $_POST['sertifika_no'];

    if (empty($ogrenci_id) || empty($kurs_id) || empty($tamamlanma_tarihi) || empty($sertifika_no)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } else {
        try {
            $stmt_check = $db->prepare("SELECT COUNT(*) FROM sertifikalar WHERE sertifika_no = ?");
            $stmt_check->execute([$sertifika_no]);
            if ($stmt_check->fetchColumn() > 0) {
                $error_message = "Bu sertifika numarası zaten mevcut. Lütfen farklı bir numara girin.";
            } else {
                $stmt = $db->prepare("INSERT INTO sertifikalar (ogrenci_id, kurs_id, tamamlanma_tarihi, sertifika_no) VALUES (?, ?, ?, ?)");
                $stmt->execute([$ogrenci_id, $kurs_id, $tamamlanma_tarihi, $sertifika_no]);
                $success_message = "Sertifika başarıyla eklendi!";
                header("Location: sertifikalar.php?success=" . urlencode($success_message));
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Sertifika eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

if (isset($_GET['sil'])) {
    $id = $_GET['sil'];
    try {
        $stmt = $db->prepare("DELETE FROM sertifikalar WHERE sertifika_id = ?");
        $stmt->execute([$id]);
        $success_message = "Sertifika başarıyla silindi!";
        header("Location: sertifikalar.php?success=" . urlencode($success_message));
        exit;
    } catch (PDOException $e) {
        $error_message = "Sertifika silinirken bir hata oluştu: " . $e->getMessage();
    }
}

$guncelle = null;
if (isset($_GET['duzenle'])) {
    $id = $_GET['duzenle'];
    try {
        $stmt = $db->prepare("SELECT sertifika_id, ogrenci_id, kurs_id, tamamlanma_tarihi, sertifika_no FROM sertifikalar WHERE sertifika_id = ?");
        $stmt->execute([$id]);
        $guncelle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$guncelle) {
            $error_message = "Düzenlenecek sertifika bulunamadı.";
        }
    } catch (PDOException $e) {
        $error_message = "Sertifika bilgileri çekilirken bir hata oluştu: " . $e->getMessage();
    }
}

if (isset($_POST['guncelle'])) {
    $id = $_POST['id'];
    $ogrenci_id = $_POST['ogrenci_id'];
    $kurs_id = $_POST['kurs_id'];
    $tamamlanma_tarihi = $_POST['tamamlanma_tarihi'];
    $sertifika_no = $_POST['sertifika_no'];

    if (empty($id) || empty($ogrenci_id) || empty($kurs_id) || empty($tamamlanma_tarihi) || empty($sertifika_no)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } else {
        try {
            $stmt_check = $db->prepare("SELECT COUNT(*) FROM sertifikalar WHERE sertifika_no = ? AND sertifika_id != ?");
            $stmt_check->execute([$sertifika_no, $id]);
            if ($stmt_check->fetchColumn() > 0) {
                $error_message = "Bu sertifika numarası başka bir sertifikada zaten mevcut. Lütfen farklı bir numara girin.";
            } else {
                $stmt = $db->prepare("UPDATE sertifikalar SET ogrenci_id=?, kurs_id=?, tamamlanma_tarihi=?, sertifika_no=? WHERE sertifika_id=?");
                $stmt->execute([$ogrenci_id, $kurs_id, $tamamlanma_tarihi, $sertifika_no, $id]);
                $success_message = "Sertifika bilgileri başarıyla güncellendi!";
                header("Location: sertifikalar.php?success=" . urlencode($success_message));
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Sertifika güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}
try {
    $sertifikalar = $db->query("
        SELECT s.sertifika_id, s.tamamlanma_tarihi, s.sertifika_no,
               CONCAT(o.ad, ' ', o.soyad) AS ogrenci_adi,
               k.kurs_adi
        FROM sertifikalar s
        JOIN ogrenciler o ON s.ogrenci_id = o.ogrenci_id
        JOIN kurslar k ON s.kurs_id = k.kurs_id
        ORDER BY s.tamamlanma_tarihi DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = (isset($error_message) && !empty($error_message)) ? $error_message : "Sertifika listesi çekilirken bir hata oluştu: " . $e->getMessage();
    $sertifikalar = [];
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifika Yönetimi - Bilge Nesil</title>
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
        .form-section input[type="email"],
        .form-section input[type="date"],
        .form-section select {
            width: calc(100% - 24px); 
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box; 
        }
        .form-section input[type="submit"] {
            width: auto; 
            padding: 12px 25px;
            background: linear-gradient(90deg, #28a745, #218838); /
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

        <h2>Sertifika Yönetimi</h2>

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
                    <th>Tarih</th>
                    <th>Öğrenci</th>
                    <th>Kurs</th>
                    <th>Sertifika No</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sertifikalar)): ?>
                    <?php foreach ($sertifikalar as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['sertifika_id']) ?></td>
                        <td><?= htmlspecialchars($s['tamamlanma_tarihi']) ?></td>
                        <td><?= htmlspecialchars($s['ogrenci_adi']) ?></td>
                        <td><?= htmlspecialchars($s['kurs_adi']) ?></td>
                        <td><?= htmlspecialchars($s['sertifika_no']) ?></td>
                        <td>
                            <a href="?duzenle=<?= htmlspecialchars($s['sertifika_id']) ?>" class="btn">Düzenle</a>
                            <a href="?sil=<?= htmlspecialchars($s['sertifika_id']) ?>" class="btn btn-red" onclick="return confirm('Bu sertifikayı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')">Sil</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Henüz sertifika bulunmamaktadır.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="form-section">
            <h3><?= $guncelle ? 'Sertifika Bilgilerini Güncelle' : 'Yeni Sertifika Ekle' ?></h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($guncelle['sertifika_id'] ?? '') ?>">

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
                            <?= htmlspecialchars($kurs['kurs_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <label for="tamamlanma_tarihi">Tamamlanma Tarihi:</label>
                <input type="date" id="tamamlanma_tarihi" name="tamamlanma_tarihi" value="<?= htmlspecialchars($guncelle['tamamlanma_tarihi'] ?? date('Y-m-d')) ?>" required><br>

                <label for="sertifika_no">Sertifika No:</label>
                <input type="text" id="sertifika_no" name="sertifika_no" placeholder="Örn: SERT-123-456" value="<?= htmlspecialchars($guncelle['sertifika_no'] ?? '') ?>" required><br>
                
                <input type="submit" name="<?= $guncelle ? 'guncelle' : 'ekle' ?>" value="<?= $guncelle ? 'Güncelle' : 'Ekle' ?>">
            </form>
        </div>
        <p class="back-link"><a href="admin.php" class="btn">Admin Paneline Geri Dön</a></p>
    </div>
</body>
</html>
