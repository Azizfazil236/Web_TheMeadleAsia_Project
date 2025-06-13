<?php

// Veritabanı bağlantı bilgileri
$host = 'localhost';          // Veritabanı sunucusu (genellikle 'localhost'tır)
$dbname = 'teknoloji_merkezi'; // Veritabanı adı
$user = 'root';               // Veritabanı kullanıcı adı
$pass = 'fazil236';                   // Veritabanı şifresi (Eğer şifreniz varsa buraya ekleyin: örn. 'fazil236')

try {
    // PDO nesnesi oluşturuluyor ve $db değişkenine atanıyor
    // Böylece diğer dosyalarda $db değişkenini kullanarak veritabanına erişebiliriz
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Hata ayıklama modu ayarlanıyor
    // Bu, veritabanı sorgularında oluşan hataların daha anlaşılır olmasını sağlar
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Bağlantı başarılıysa buraya ulaşır
    // echo "Bağlantı başarılı"; // Deneme amaçlıdır, canlı sistemde yorum satırı yapılabilir veya kaldırılabilir
} catch (PDOException $e) {
    // Bağlantı hatası durumunda hata mesajı yazdırılır ve uygulamanın çalışması durdurulur
    // Bu, hassas veritabanı bilgilerinin kullanıcıya gösterilmesini engeller ve hatayı takip etmenizi sağlar
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

?>