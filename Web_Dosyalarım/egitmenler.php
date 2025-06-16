<?php
session_start(); 

require 'db.php';
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

$error_message = ""; 
$success_message = ""; 

if (isset($_POST['ekle'])) {
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $telefon = trim($_POST['telefon']);
    $email = trim($_POST['email']);
    $uzmanlik_alani_1 = $_POST['uzmanlik_alani_1'];
    $pozisyon = $_POST['pozisyon'];
    $baslama_tarihi = $_POST['baslama_tarihi'];
    $cinsiyet = $_POST['cinsiyet'];
    $password = $_POST['password'] ?? '';
    $hashed_password = null;

    if (empty($ad) || empty($soyad) || empty($telefon) || empty($uzmanlik_alani_1) || empty($pozisyon) || empty($baslama_tarihi) || empty($cinsiyet)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $error_message = "Geçerli bir e-posta adresi girin.";
    } else {
        try {
            $stmt_check_email = $db->prepare("SELECT COUNT(*) FROM egitmenler WHERE email = ?");
            $stmt_check_email->execute([$email]);
            if ($stmt_check_email->fetchColumn() > 0) {
                $error_message = "Bu e-posta adresi zaten kullanımda.";
            } else {
                
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                }

                $stmt = $db->prepare("INSERT INTO egitmenler (ad, soyad, telefon, email, uzmanlik_alani_1, pozisyon, baslama_tarihi, cinsiyet, password, durum, kayit_tarihi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif', CURRENT_TIMESTAMP)");
                $stmt->execute([$ad, $soyad, $telefon, $email, $uzmanlik_alani_1, $pozisyon, $baslama_tarihi, $cinsiyet, $hashed_password]);
                
                $success_message = "Eğitmen başarıyla eklendi!";
                header("Location: egitmenler.php?success=" . urlencode($success_message));
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Eğitmen eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

if (isset($_GET['sil'])) {
    $id = $_GET['sil'];
    try {
        $stmt = $db->prepare("DELETE FROM egitmenler WHERE egitmen_id = ?");
        $stmt->execute([$id]);
        $success_message = "Eğitmen başarıyla silindi!";
        header("Location: egitmenler.php?success=" . urlencode($success_message));
        exit;
    } catch (PDOException $e) {
        $error_message = "Eğitmen silinirken bir hata oluştu: " . $e->getMessage();
    }
}

$guncelle = null;
if (isset($_GET['duzenle'])) {
    $id = $_GET['duzenle'];
    try {
        $stmt = $db->prepare("SELECT egitmen_id, ad, soyad, telefon, email, uzmanlik_alani_1, uzmanlik_alani_2, pozisyon, baslama_tarihi, cinsiyet, durum FROM egitmenler WHERE egitmen_id = ?");
        $stmt->execute([$id]);
        $guncelle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$guncelle) {
            $error_message = "Düzenlenecek eğitmen bulunamadı.";
        }
    } catch (PDOException $e) {
        $error_message = "Eğitmen bilgileri çekilirken bir hata oluştu: " . $e->getMessage();
    }
}

// Güncelleme işlemi
if (isset($_POST['guncelle'])) {
    $id = $_POST['id'];
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $telefon = trim($_POST['telefon']);
    $email = trim($_POST['email']);
    $uzmanlik_alani_1 = $_POST['uzmanlik_alani_1'];
    // $uzmanlik_alani_2 = $_POST['uzmanlik_alani_2']; // Formda yok, bu yüzden kaldırıldı
    $pozisyon = $_POST['pozisyon'];
    $baslama_tarihi = $_POST['baslama_tarihi'];
    $cinsiyet = $_POST['cinsiyet'];
    $durum = $_POST['durum'];
    $password = $_POST['password'] ?? ''; // Şifre alanı alındı
    $hashed_password_for_update = null;


    if (empty($ad) || empty($soyad) || empty($telefon) || empty($uzmanlik_alani_1) || empty($pozisyon) || empty($baslama_tarihi) || empty($cinsiyet) || empty($id)) {
        $error_message = "Lütfen tüm zorunlu alanları doldurun.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $error_message = "Geçerli bir e-posta adresi girin.";
    } else {
        try {
    
            $stmt_check_email = $db->prepare("SELECT COUNT(*) FROM egitmenler WHERE email = ? AND egitmen_id != ?");
            $stmt_check_email->execute([$email, $id]);
            if ($stmt_check_email->fetchColumn() > 0) {
                $error_message = "Bu e-posta adresi zaten başka bir eğitmene kayıtlı.";
            } else {
                
                if (!empty($password)) {
                    $hashed_password_for_update = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE egitmenler SET ad=?, soyad=?, telefon=?, email=?, uzmanlik_alani_1=?, pozisyon=?, baslama_tarihi=?, cinsiyet=?, durum=?, password=? WHERE egitmen_id=?");
                    $stmt->execute([$ad, $soyad, $telefon, $email, $uzmanlik_alani_1, $pozisyon, $baslama_tarihi, $cinsiyet, $durum, $hashed_password_for_update, $id]);
                } else { 
                    $stmt = $db->prepare("UPDATE egitmenler SET ad=?, soyad=?, telefon=?, email=?, uzmanlik_alani_1=?, pozisyon=?, baslama_tarihi=?, cinsiyet=?, durum=? WHERE egitmen_id=?");
                    $stmt->execute([$ad, $soyad, $telefon, $email, $uzmanlik_alani_1, $pozisyon, $baslama_tarihi, $cinsiyet, $durum, $id]);
                }
                
                $success_message = "Eğitmen bilgileri başarıyla güncellendi!";
                header("Location: egitmenler.php?success=" . urlencode($success_message)); 
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Eğitmen güncellenirken bir hata oluştu: " . $e->getMessage();
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
    $egitmenler = $db->query("SELECT egitmen_id, ad, soyad, telefon, email, uzmanlik_alani_1, pozisyon, baslama_tarihi, durum FROM egitmenler ORDER BY baslama_tarihi DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = (isset($error_message) && !empty($error_message)) ? $error_message : "Eğitmen listesi çekilirken bir hata oluştu: " . $e->getMessage();
    $egitmenler = []; 
}


$uzmanlik_alanlari = [
    'Yazılım Geliştirme', 'Web Tasarım', 'Robotik', 'Elektronik',
    'Veri Analizi', 'Siber Güvenlik', 'Mobil Uygulama', 'Oyun Geliştirme'
];


$pozisyonlar = ['Gönüllü', 'Yarı Zamanlı', 'Tam Zamanlı'];

$durumlar = ['Aktif', 'İzinli', 'Ayrılmış'];

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eğitmen Yönetimi - Bilge Nesil</title>
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
        .form-section input[type="password"], 
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
            background: linear-gradient(90deg, #28a745, #218838); 
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

        <h2>Eğitmen Yönetimi</h2>

        <?php if (!empty($error_message)): ?>
            <div class="message-box error-message"><?= $error_message ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="message-box success-message"><?= $success_message ?></div>
        <?php endif; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Ad Soyad</th>
                <th>Telefon</th>
                <th>Email</th>
                <th>Uzmanlık Alanı</th>
                <th>Pozisyon</th>
                <th>Başlama Tarihi</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
            <?php if (!empty($egitmenler)): ?>
                <?php foreach ($egitmenler as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['egitmen_id']) ?></td>
                    <td><?= htmlspecialchars($e['ad'] . ' ' . $e['soyad']) ?></td>
                    <td><?= htmlspecialchars($e['telefon']) ?></td>
                    <td><?= htmlspecialchars($e['email']) ?></td>
                    <td><?= htmlspecialchars($e['uzmanlik_alani_1']) ?></td>
                    <td><?= htmlspecialchars($e['pozisyon']) ?></td>
                    <td><?= htmlspecialchars($e['baslama_tarihi']) ?></td>
                    <td><?= htmlspecialchars($e['durum']) ?></td>
                    <td>
                        <a href="?duzenle=<?= htmlspecialchars($e['egitmen_id']) ?>" class="btn">Düzenle</a>
                        <a href="?sil=<?= htmlspecialchars($e['egitmen_id']) ?>" class="btn btn-red" onclick="return confirm('Eğitmeni silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center;">Henüz kayıtlı eğitmen bulunmamaktadır.</td>
                </tr>
            <?php endif; ?>
        </table>

        <div class="form-section">
            <h3><?= $guncelle ? 'Eğitmen Bilgilerini Güncelle' : 'Yeni Eğitmen Ekle' ?></h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($guncelle['egitmen_id'] ?? '') ?>">

                <label for="ad">Ad:</label>
                <input type="text" id="ad" name="ad" value="<?= htmlspecialchars($guncelle['ad'] ?? '') ?>" required><br>

                <label for="soyad">Soyad:</label>
                <input type="text" id="soyad" name="soyad" value="<?= htmlspecialchars($guncelle['soyad'] ?? '') ?>" required><br>

                <label for="cinsiyet">Cinsiyet:</label>
                <select id="cinsiyet" name="cinsiyet" required>
                    <option value="">Seçiniz</option>
                    <option value="Erkek" <?= isset($guncelle) && $guncelle['cinsiyet'] == 'Erkek' ? 'selected' : '' ?>>Erkek</option>
                    <option value="Kadın" <?= isset($guncelle) && $guncelle['cinsiyet'] == 'Kadın' ? 'selected' : '' ?>>Kadın</option>
                </select><br>

                <label for="telefon">Telefon:</label>
                <input type="text" id="telefon" name="telefon" value="<?= htmlspecialchars($guncelle['telefon'] ?? '') ?>" required><br>

                <label for="email">E-posta:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($guncelle['email'] ?? '') ?>"><br>
                
                <label for="password">Şifre (Boş bırakılırsa değişmez):</label>
                <input type="password" id="password" name="password" placeholder="Şifreyi değiştir" autocomplete="new-password"><br>


                <label for="uzmanlik_alani_1">Uzmanlık Alanı:</label>
                <select id="uzmanlik_alani_1" name="uzmanlik_alani_1" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($uzmanlik_alanlari as $alan): ?>
                        <option value="<?= htmlspecialchars($alan) ?>" <?= isset($guncelle) && $guncelle['uzmanlik_alani_1'] == $alan ? 'selected' : '' ?>>
                            <?= htmlspecialchars($alan) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <label for="pozisyon">Pozisyon:</label>
                <select id="pozisyon" name="pozisyon" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($pozisyonlar as $pos): ?>
                        <option value="<?= htmlspecialchars($pos) ?>" <?= isset($guncelle) && $guncelle['pozisyon'] == $pos ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pos) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <label for="baslama_tarihi">Başlama Tarihi:</label>
                <input type="date" id="baslama_tarihi" name="baslama_tarihi" value="<?= htmlspecialchars($guncelle['baslama_tarihi'] ?? date('Y-m-d')) ?>" required><br>

                <?php if ($guncelle): 
                    <label for="durum">Durum:</label>
                    <select id="durum" name="durum" required>
                        <?php foreach ($durumlar as $d): ?>
                            <option value="<?= htmlspecialchars($d) ?>" <?= isset($guncelle) && $guncelle['durum'] == $d ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d) ?>
                            </option>
                        <?php endforeach; ?>
                    </select><br>
                <?php endif; ?>

                <input type="submit" name="<?= $guncelle ? 'guncelle' : 'ekle' ?>" value="<?= $guncelle ? 'Güncelle' : 'Ekle' ?>">
            </form>
        </div>
        <p class="back-link"><a href="admin.php" class="btn">Admin Paneline Geri Dön</a></p>
    </div>
</body>
</html>
