<?php
require_once "baglanti.php";
session_start();
// Doğrulama kodunu ve e-posta adresini alın
if (isset($_GET['email']) && isset($_GET['kod'])) {
    $email = $_GET['email'];
    $dogrulama_kodu = $_GET['kod'];

    // Veritabanında doğrulama kodunu kontrol et
    $kontrol = "SELECT * FROM kullanicilar WHERE email = ? AND dogrulama_kodu = ? AND verified = 0";
    if ($stmt = mysqli_prepare($conn, $kontrol)) {
        mysqli_stmt_bind_param($stmt, "ss", $email, $dogrulama_kodu);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            // Doğrulama kodu doğru, kullanıcıyı onayla
            $update = "UPDATE kullanicilar SET verified = 1 WHERE email = ? AND dogrulama_kodu = ?";
            if ($stmt2 = mysqli_prepare($conn, $update)) {
                mysqli_stmt_bind_param($stmt2, "ss", $email, $dogrulama_kodu);
                if (mysqli_stmt_execute($stmt2)) {
                    echo "<div class='alert alert-success'>Hesabınız başarıyla doğrulandı!</div>";
                    header("Refresh:2; url=login.php"); // 3 saniye içinde giriş sayfasına yönlendirme
                } else {
                    echo "<div class='alert alert-danger'>Doğrulama işlemi sırasında hata oluştu. Lütfen daha sonra tekrar deneyin.</div>";
                }
                mysqli_stmt_close($stmt2);
            }
        } else {
            echo "<div class='alert alert-danger'>Geçersiz doğrulama kodu veya e-posta adresi.</div>";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-posta Doğrulama</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body >
<style>
     body {
        background-color: #c8c7c7;}
</style>
<div class="container mt-5">
    <div class="alert alert-info">
        Lütfen e-posta adresinizi kontrol edin ve doğrulama bağlantısını tıklayın.
    </div>

    <form action="dogrulama.php" method="GET">
        <div class="form-group">
            <label for="email">E-posta adresiniz:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="kod">Doğrulama Kodu:</label>
            <input type="text" class="form-control" id="kod" name="kod" required>
        </div>
        <button type="submit" class="btn btn-primary">Doğrula</button>
    </form>
</div>

</body>
</html>
