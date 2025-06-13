<?php
session_start();
require 'db.php'; // Veritabanı bağlantısı için

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

$admin_adi = $_SESSION["username"] ?? "Admin";
$error_message = "";

// Veritabanı sorgularını tek bir try-catch bloğunda toplayalım
try {
    $data_sections = [
        "ogrenciler" => [
            "title" => "Öğrenci Yönetimi",
            "icon" => "👨‍🎓",
            "query" => "SELECT ogrenci_id, ad, soyad, yas, teknoloji_seviyesi FROM ogrenciler WHERE durum = 'Aktif' ORDER BY kayit_tarihi DESC LIMIT 5",
            "headers" => ["ID", "Ad Soyad", "Yaş", "Teknoloji Seviyesi"],
            "map_func" => fn($row) => [
                $row['ogrenci_id'],
                $row['ad'] . ' ' . $row['soyad'],
                $row['yas'],
                $row['teknoloji_seviyesi']
            ],
            "link" => "ogrenciler.php"
        ],
        "egitmenler" => [
            "title" => "Eğitmen Yönetimi",
            "icon" => "👨‍🏫", // Eğitmen için daha uygun bir ikon
            "query" => "SELECT egitmen_id, ad, soyad, uzmanlik_alani_1, pozisyon FROM egitmenler WHERE durum = 'Aktif' ORDER BY baslama_tarihi DESC LIMIT 5",
            "headers" => ["ID", "Ad Soyad", "Uzmanlık Alanı", "Pozisyon"],
            "map_func" => fn($row) => [
                $row['egitmen_id'],
                $row['ad'] . ' ' . $row['soyad'],
                $row['uzmanlik_alani_1'],
                $row['pozisyon']
            ],
            "link" => "egitmenler.php"
        ],
        "kurslar" => [
            "title" => "Kurs Yönetimi",
            "icon" => "📘",
            "query" => "SELECT kurs_id, kurs_kodu, kurs_adi, kategori, seviye, durum FROM kurslar WHERE durum IN ('Kayıt Açık', 'Başladı') ORDER BY baslangic_tarihi DESC LIMIT 5",
            "headers" => ["Kurs Kodu", "Kurs Adı", "Kategori", "Seviye", "Durum"],
            "map_func" => fn($row) => [
                $row['kurs_kodu'],
                $row['kurs_adi'],
                $row['kategori'],
                $row['seviye'],
                $row['durum']
            ],
            "link" => "kurslar.php"
        ],
        "kayitlar" => [
            "title" => "Kayıtlar Yönetimi",
            "icon" => "📝",
            "query" => "SELECT kay.kayit_id, kay.kayit_tarihi, kay.durum, CONCAT(o.ad, ' ', o.soyad) AS ogrenci_adi, k.kurs_adi FROM kayitlar kay JOIN ogrenciler o ON kay.ogrenci_id = o.ogrenci_id JOIN kurslar k ON kay.kurs_id = k.kurs_id ORDER BY kay.kayit_tarihi DESC LIMIT 5",
            "headers" => ["Kayıt ID", "Öğrenci", "Kurs", "Kayıt Tarihi", "Durum"],
            "map_func" => fn($row) => [
                $row['kayit_id'],
                $row['ogrenci_adi'],
                $row['kurs_adi'],
                $row['kayit_tarihi'],
                $row['durum']
            ],
            "link" => "kayitlar.php"
        ],
        "devam" => [
            "title" => "Devam Takibi",
            "icon" => "📅",
            "query" => "SELECT d.devam_id, d.tarih, d.katilim_durumu, CONCAT(o.ad, ' ', o.soyad) AS ogrenci_adi, k.kurs_adi FROM devam d JOIN ogrenciler o ON d.ogrenci_id = o.ogrenci_id JOIN kurslar k ON d.kurs_id = k.kurs_id ORDER BY d.tarih DESC LIMIT 5",
            "headers" => ["Öğrenci", "Kurs", "Tarih", "Durum"],
            "map_func" => fn($row) => [
                $row['ogrenci_adi'],
                $row['kurs_adi'],
                $row['tarih'],
                $row['katilim_durumu']
            ],
            "link" => "devam.php"
        ],
        "sertifikalar" => [
            "title" => "Sertifikalar",
            "icon" => "📄",
            // SERTİFİKA sorgusunu 'tamamlanma_tarihi' ile güncelledik
            "query" => "SELECT s.sertifika_id, s.sertifika_no, s.tamamlanma_tarihi, CONCAT(o.ad, ' ', o.soyad) AS ogrenci_adi, k.kurs_adi FROM sertifikalar s JOIN ogrenciler o ON s.ogrenci_id = o.ogrenci_id JOIN kurslar k ON s.kurs_id = k.kurs_id ORDER BY s.tamamlanma_tarihi DESC LIMIT 5",
            "headers" => ["Sertifika ID", "Öğrenci", "Kurs", "Sertifika No", "Tamamlanma Tarihi"], // Header'ı da güncelledik
            "map_func" => fn($row) => [
                $row['sertifika_id'], // ID'yi de ekledik
                $row['ogrenci_adi'],
                $row['kurs_adi'],
                $row['sertifika_no'],
                $row['tamamlanma_tarihi']
            ],
            "link" => "sertifikalar.php"
        ]
    ];

    foreach ($data_sections as $key => &$section) {
        $stmt = $db->query($section['query']);
        $section['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Veritabanı bağlantısında veya sorgularında bir hata oluştu: " . $e->getMessage();
    foreach ($data_sections as $key => &$section) {
        $section['data'] = [];
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Bilge Nesil</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f0f4f8; color: #1a3c5e; display: flex; flex-direction: column; min-height: 100vh; }
        .header { background: #007bff; color: white; padding: 20px 30px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .header h2 { margin: 0; font-size: 2.2em; }
        .header p { margin-top: 10px; font-size: 1.1em; }
        .main-content { max-width: 1200px; margin: 30px auto; padding: 0 20px; flex-grow: 1; }
        .section { background: white; padding: 25px 30px; border-radius: 12px; box-shadow: 0 6px 15px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .section h3 { color: #007bff; margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .section h3 span { font-size: 1.5em; line-height: 1; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
        th, td { border: 1px solid #e0e6ed; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9fbfd; }
        tr:hover { background: #eef7ff; }
        a { color: #007bff; text-decoration: none; transition: color 0.3s ease; }
        a:hover { text-decoration: underline; color: #0056b3; }
        .btn-action {
            display: inline-block;
            padding: 9px 18px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            transition: background 0.3s ease, transform 0.2s ease;
            margin-top: 15px;
            margin-right: 10px;
        }
        .btn-action:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .btn-logout {
            display: inline-block;
            padding: 10px 25px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.1em;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn-logout:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
        }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .footer { text-align: center; padding: 20px; margin-top: auto; background: #e0e6ed; color: #5e7a9c; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Bilge Nesil Teknoloji Merkezi Admin Paneli</h2>
        <p>Hoş Geldiniz, <?= htmlspecialchars($admin_adi) ?>.</p>
        <a href="logout.php" class="btn-logout">Çıkış Yap</a>
    </div>

    <div class="main-content">
        <?php if (!empty($error_message)): ?>
            <div class="message-box error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php foreach ($data_sections as $section_key => $section_info): ?>
            <div class="section">
                <h3><span><?= $section_info['icon'] ?></span> <?= htmlspecialchars($section_info['title']) ?></h3>
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($section_info['headers'] as $header): ?>
                                <th><?= htmlspecialchars($header) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($section_info['data'])): ?>
                            <?php foreach ($section_info['data'] as $row): ?>
                                <tr>
                                    <?php foreach ($section_info['map_func']($row) as $cell_data): ?>
                                        <td><?= htmlspecialchars($cell_data) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= count($section_info['headers']) ?>" style="text-align: center;">
                                    Henüz kayıt bulunmamaktadır.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="<?= htmlspecialchars($section_info['link']) ?>" class="btn-action">
                    <?= htmlspecialchars($section_info['title']) ?> Yönetimine Git
                </a>
            </div>
        <?php endforeach; ?>

    </div>
    
    <div class="footer">
        &copy; <?= date("Y") ?> Bilge Nesil Teknoloji Merkezi. Tüm Hakları Saklıdır.
    </div>
</body>
</html>