<?php
// Veritabanı bağlantı bilgileri
$host = "localhost";        // Sunucu adresi
$kullanici = "u0914930_memoliadmin"; // Kullanıcı adı
$password = "DM=8KPVZ{gNc"; // Şifre
$vt = "u0914930_uyelik2";   // Veritabanı adı

// MySQL bağlanti
$conn = mysqli_connect($host, $kullanici, $password, $vt);

// Bağlantı kontrolü
if (!$conn) {
    die("Veritabanına bağlanırken hata oluştu: " . mysqli_connect_error());
}
?>
