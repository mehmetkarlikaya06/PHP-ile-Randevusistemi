<?php
session_start();

// Veritabanı bağlantısı
$host = "localhost";
$kullanici = "u0914930_memoliadmin";
$password = "DM=8KPVZ{gNc";
$vt = "u0914930_uyelik2";

try {
    $db = new PDO("mysql:host=$host;dbname=$vt;charset=utf8", $kullanici, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Veritabanı bağlantısı başarısız: " . $e->getMessage();
    exit;
}

// Formdan gelen verileri al
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $randevu_tarihi = $_POST['randevu_tarihi'];
    $randevu_saati = $_POST['randevu_saati'];
    $kullanici_adi = $_SESSION["usurname"]; // Kullanıcı adını oturumdan al

    // Randevu ekleme sorgusu
    $query = "INSERT INTO randevular (kullanici_adi, randevu_tarihi, randevu_saati) VALUES (:kullanici_adi, :randevu_tarihi, :randevu_saati)";
    $statement = $db->prepare($query);
    $statement->bindParam(":kullanici_adi", $kullanici_adi);
    $statement->bindParam(":randevu_tarihi", $randevu_tarihi);
    $statement->bindParam(":randevu_saati", $randevu_saati);

    if ($statement->execute()) {
        echo "Randevunuz başarıyla alındı.";
    } else {
        echo "Randevu alırken bir hata oluştu.";
    }
} else {
    echo "Geçersiz istek.";
}
?>