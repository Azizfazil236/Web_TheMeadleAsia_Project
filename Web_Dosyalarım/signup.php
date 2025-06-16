<?php
include 'db.php'; // db.php PDO bağlantınızı içeriyor


$error = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = trim($_POST["ad"]); 
    $soyad = trim($_POST["soyad"]); 
    $email = trim($_POST["email"]); 
    $password = $_POST["password"];
    $telefon = trim($_POST["telefon"]); 
    $olusturan_admin = "self";
    
    if (empty($ad) || empty($soyad) || empty($email) || empty($password)) {
        $error = "Lütfen tüm zorunlu alanları doldurun.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir e-posta adresi girin.";
    } elseif (strlen($password) < 6) { // Şifre uzunluğu kontrolü
        $error = "Şifreniz en az 6 karakter olmalıdır.";
    } else {
        try {
            $check_email_query = $db->prepare("SELECT COUNT(*) FROM egitmenler WHERE email = ?");
            $check_email_query->execute([$email]);
            if ($check_email_query->fetchColumn() > 0) {
                $error = "Bu e-posta adresi zaten kullanımda.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                try {
                    $sorgu = $db->prepare("INSERT INTO egitmenler (ad, soyad, email, password, telefon, olusturan_admin, baslama_tarihi, pozisyon) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), 'Gönüllü')");
                    $sorgu->execute([$ad, $soyad, $email, $hashed_password, $telefon, $olusturan_admin]);
                    $success_message = "Kayıt başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz.";
                } catch (PDOException $e) {
                    $error = "Kayıt işlemi sırasında bir veritabanı hatası oluştu: " . $e->getMessage();
                }
            }
        } catch (PDOException $e) {
            $error = "E-posta kontrolü sırasında bir veritabanı hatası oluştu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eğitmen Kayıt - Bilge Nesil</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            height: 120vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: url('background4.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #1a3c5e;
            text-align: center;
        }
        .container {
            max-width: 450px;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px; 
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            width: 90%;
            box-sizing: border-box;
        }
        h2 {
            font-size: 28px; 
            margin-bottom: 0px;
            color: #007bff;
        }
        form {
            text-align: left;
        }
        label {
            display: block;
            margin: 12px 0 6px; 
            color: #1a3c5e;
            font-weight: bold; 
            font-size: 15px;
        }
        input {
            width: calc(100% - 24px); 
            padding: 12px; 
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 14px; 
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 17px; 
            margin-top: 20px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: linear-gradient(90deg, #0056b3, #003d80);
        }
        .links {
            text-align: center;
            margin-top: 25px;
        }
        .links a {
            color: #007bff;
            text-decoration: none;
            font-size: 15px;
            font-weight: bold;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .error {
            color: #dc3545;
            margin-bottom: 15px;
            font-size: 15px;
            text-align: center;
            font-weight: bold;
        }
        .success {
            color: #28a745; /* Yeşil renk */
            margin-bottom: 15px;
            font-size: 15px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Eğitmen Kayıt</h2>
        <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
        <?php if (!empty($success_message)) { echo "<p class='success'>$success_message</p>"; } ?>

        <form method="POST">
            <label for="ad">Ad:</label>
            <input type="text" id="ad" name="ad" required autocomplete="given-name">

            <label for="soyad">Soyad:</label>
            <input type="text" id="soyad" name="soyad" required autocomplete="family-name">

            <label for="email">E-posta:</label>
            <input type="email" id="email" name="email" required autocomplete="email">

            <label for="password">Şifre:</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">

            <label for="telefon">Telefon (İsteğe Bağlı):</label>
            <input type="text" id="telefon" name="telefon" autocomplete="tel">

            <button type="submit">Kayıt Ol</button>
        </form>
        <div class="links">
            <p><a href="login.php">Zaten Hesabım Var, Giriş Yap</a></p>
        </div>
    </div>
</body>
</html>
