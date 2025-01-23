<?php

function check_admin() {
    // Kullanıcı session bilgisi yoksa veya user_type admin değilse giriş sayfasına yönlendir
    if (!isset($_SESSION['user_typ']) || $_SESSION['user_typ'] !== 'admin') {
        header("Location: login.php"); // Kullanıcı giriş sayfasına yönlendirilir
        exit(); // Fonksiyon sonlandırılır
    }
}
?>
