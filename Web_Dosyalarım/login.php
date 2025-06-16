<?php
session_start();
include 'db.php'; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    try {
        $sorgu = $db->prepare("SELECT * FROM egitmenler WHERE email = ?");
        $sorgu->execute([$email]);
        $egitmen = $sorgu->fetch(PDO::FETCH_ASSOC);

        if ($egitmen && password_verify($password, $egitmen["password"])) {
            $_SESSION["user_id"] = $egitmen["egitmen_id"];
            $_SESSION["role"] = "admin";
            header("Location: admin.php");
            exit;
        } else {
            $error = "Hatalı e-posta veya şifre!";
        }
    } catch (PDOException $e) {
        $error = "Veritabanı hatası: " . $e->getMessage(); 
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilge Nesil Teknoloji Merkezi - Giriş</title>
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
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 90%; 
            box-sizing: border-box;
        }
        h1 {
            font-size: 30px;
            margin-bottom: 0px;
            color: #007bff;
        }
        p {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .btn {
            padding: 12px 24px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            display: inline-block; 
            margin-top: 15px; 
        }
        .btn:hover {
            background: linear-gradient(90deg, #0056b3, #003d80);
        }
        .logo img {
            width: 150px;
            margin-bottom: 0px;
        }
        .login-form {
            margin-top: 0px;
            text-align: left;
        }
        .login-form h2 {
            color: #1a3c5e;
            margin-bottom: 20px;
        }
        .login-form input {
            width: calc(100% - 24px); 
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        .login-form button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .login-form button:hover {
            background: linear-gradient(90deg, #0056b3, #003d80);
        }
        .error {
            color: #dc3545;
            margin-bottom: 15px;
            font-size: 15px;
            text-align: center;
            font-weight: bold;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px; 
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="Logo2.png">
        </div>
        <h1>Bilge Nesil Teknoloji Merkezi</h1>
        <p>Asya-yı Miyane'deki gençler için bir gelecek vizyonu!</p>

        <div class="login-form">
            <h2 style="text-align:center;">Admin Girişi</h2>
            <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
            <form method="POST">
                <input type="email" name="email" placeholder="E-posta" required autocomplete="username">
                <input type="password" name="password" placeholder="Şifre" required autocomplete="current-password">
                <button type="submit">Giriş Yap</button>
            </form>
            <div class="links">
                <a href="signup.php">Kayıt Ol</a> | <a href="#">Şifremi Unuttum</a>
            </div>
        </div>
    </div>
</body>
</html>
