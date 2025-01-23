<?php
session_start();
include("baglanti.php"); // Veritabanı bağlantısını dahil et

// Hata mesajlarını tanımla
$usurname_err = $parola_err = "";
$usurname = $parola = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kullanıcı adı doğrulama
    if (empty($_POST["kullaniciadi"])) {
        $usurname_err = "Kullanıcı Adı Boş Geçilemez";
    } else {
        $usurname = mysqli_real_escape_string($conn, $_POST["kullaniciadi"]);
    }

    // Parola doğrulama
    if (empty($_POST["parola"])) {
        $parola_err = "Parola Boş Geçilemez";
    } else {
        $parola = $_POST["parola"];
    }

    // Giriş yapmaya hazırsa
    if (empty($usurname_err) && empty($parola_err)) {
        $secim = "SELECT * FROM kullanicilar WHERE kullanici_adi = ?";
        $stmt = mysqli_prepare($conn, $secim);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $usurname);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                if (password_verify($parola, $row["parola"])) {
                    // Kullanıcı bilgilerini oturuma kaydet
                    $_SESSION["usurname"] = $row["kullanici_adi"];
                    $_SESSION["email"] = $row["email"];
                    $_SESSION["user_typ"] = $row["user_typ"];
                    $_SESSION["verified"] = $row["verified"];
                    // Doğrulama durumunu oturuma kaydet
                    if ($_SESSION["verified"] == 1) {

                        echo "Hesabınız doğrulanmıştır";
                        echo '<script>
                            setTimeout(function() {
                                // Kullanıcı tipi kontrolü ve yönlendirme
                                if ("' . $row["user_typ"] . '" === "admin") {
                                    window.location.href = "admin.php"; // Admin için yönlendirme
                                } else {
                                    window.location.href = "profile.php"; // Normal kullanıcı için yönlendirme
                                }
                            }, 0); // 5 saniye sonra yönlendirme
                        </script>';
                        exit();
                    }
                } else {
                    $parola_err = "Parola hatalı.";
                }
            } else {
                $usurname_err = "Kullanıcı bulunamadı.";
            }
        } else {
            die("Sorgu hazırlama hatası: " . mysqli_error($conn));
        }
    }
}
?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Üye Giriş İşlemi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #c8c7c7;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 400px;
            width: 100%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card {
            padding: 20px;
        }

        .card form {
            display: flex;
            flex-direction: column;
        }

        .card .mb-4, .card .mb-8 {
            margin-bottom: 15px;
        }

        .card label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card input[type="text"],
        .card input[type="password"] {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            width: 100%;
        }

        .card input:focus {
            border-color: #80bdff;
            outline: none;
            box-shadow: 0 0 4px rgba(128, 189, 255, 0.5);
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        button[type="submit"],
        .btn-secondary {
            display: inline-block;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            text-align: center;
            border: none;
            margin-top: 10px;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <form action="login.php" method="POST">
            <div class="mb-4">
                <label for="exampleInputEmail1" class="form-label">Kullanıcı Adı</label>
                <input type="text" class="form-control <?php echo $usurname_err ? 'is-invalid' : ''; ?>" id="exampleInputEmail1" name="kullaniciadi">
                <div class="invalid-feedback">
                    <?php echo $usurname_err; ?>
                </div>
            </div>

            <div class="mb-8">
                <label for="exampleInputPassword1" class="form-label">Parola</label>
                <input type="password" class="form-control <?php echo $parola_err ? 'is-invalid' : ''; ?>" id="exampleInputPassword1" name="parola">
                <div class="invalid-feedback">
                    <?php echo $parola_err; ?>
                </div>
            </div>

            <button type="submit" name="giris" class="btn btn-primary">Giriş Yap</button>
            <a href="kayit.php" class="btn btn-secondary" role="button">Kayıt Ol</a> <!-- Kayıt Ol butonu -->

        </form>
    </div>
</div>
</body>
</html>
