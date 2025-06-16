<?php


$host = 'localhost';          
$dbname = 'teknoloji_merkezi'; 
$user = 'root';               
$pass = 'fazil236';                  
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
 
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

?>
