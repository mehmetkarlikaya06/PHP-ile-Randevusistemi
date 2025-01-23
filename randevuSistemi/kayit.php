<?php
// Hata raporlamayı etkinleştir
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('baglanti.php'); // baglanti.php dosyasını dahil edin
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Hata mesajlarını tanımla
$usurname_err = "";
$email_err = "";
$parola_err = "";
$parolatkr_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kullanıcı adı doğrulama (Boş bırakılmasın, karakter sınırlaması olmadan)
    if (empty(trim($_POST["kullaniciadi"]))) {
        $usurname_err = "Kullanıcı Adı Boş Geçilemez";
    } else {
        $usurname = trim($_POST["kullaniciadi"]);
    }

    // E-posta doğrulama
    if (empty($_POST["email"])) {
        $email_err = "Email Boş Geçilemez";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_err = "Geçersiz Email Formatı";
    } else {
        $email = trim($_POST["email"]);
    }

    // Parola doğrulama
    if (empty($_POST["parola"])) {
        $parola_err = "Parola Boş Geçilemez";
    } else {
        $parola = password_hash($_POST["parola"], PASSWORD_DEFAULT);
    }

    // Parola tekrar doğrulama
    if (empty($_POST["parolatkr"])) {
        $parolatkr_err = "Parola Tekrarı Boş Geçilemez";
    } elseif ($_POST["parolatkr"] != $_POST["parola"]) {
        $parolatkr_err = "Parolalar eşleşmiyor";
        $parola = $parolatkr = "";
    } else {
        $parolatkr = $_POST["parolatkr"];
    }

    // Tüm alanlar doğruysa veritabanına ekleme
    if (empty($usurname_err) && empty($email_err) && empty($parola_err) && empty($parolatkr_err)) {
        $dogrulama_kodu = rand(100000, 999999); // 6 haneli rastgele bir doğrulama kodu

        // Veritabanına kayıt işlemi
        $ekle = "INSERT INTO kullanicilar (kullanici_adi, email, parola, dogrulama_kodu) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $ekle)) {
            mysqli_stmt_bind_param($stmt, "ssss", $usurname, $email, $parola, $dogrulama_kodu);
            if (mysqli_stmt_execute($stmt)) {
                // Mail gönderme
                sendVerificationEmail($email, $dogrulama_kodu);
                header("Location:dogrulama.php?email=" . $email);
                exit();
            } else {
                echo '<div class="alert alert-danger">Kayıt eklenirken hata oluştu.</div>';
            }
            mysqli_stmt_close($stmt);
        } else {
            echo '<div class="alert alert-danger">Hazırlık hatası.</div>';
        }
    }
}

// Mail Gönderme Fonksiyonu
function sendVerificationEmail($email, $dogrulama_kodu) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'karlikaya0516@gmail.com'; // Kullanıcı Gmail hesabı
        $mail->Password   = 'zvwzcjhzzindcfwd'; // Gmail API veya şifre
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Gönderici bilgisi
        $mail->setFrom('karlikaya0516@gmail.com', '');
        $mail->addAddress($email, $email); // Yalnızca ilgili kullanıcıya e-posta gönder
        $mail->isHTML(true);
        $mail->Subject = 'Email verification';
        $mail->Body    = '<p>Dogrulama kodunuz: <b style="font-size: 30px">' . $dogrulama_kodu . '<br></p>';

        $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Üye Kayıt İşlemi</title>
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
        margin: 5px;
        padding: 18px 27px;
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
            <form action="kayit.php" method="POST">
                <div class="mb-3">
                    <label for="kullaniciadi" class="form-label">Kullanıcı Adı</label>
                    <input type="text" class="form-control <?php echo !empty($usurname_err) ? 'is-invalid' : ''; ?>" id="kullaniciadi" name="kullaniciadi" value="<?php echo isset($usurname) ? $usurname : ''; ?>">
                    <div class="invalid-feedback">
                        <?php echo $usurname_err; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control <?php echo !empty($email_err) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>">
                    <div class="invalid-feedback">
                        <?php echo $email_err; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="parola" class="form-label">Parola</label>
                    <input type="password" class="form-control <?php echo !empty($parola_err) ? 'is-invalid' : ''; ?>" id="parola" name="parola" value="<?php echo isset($parola) ? $parola : ''; ?>">
                    <div class="invalid-feedback">
                        <?php echo $parola_err; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="parolatkr" class="form-label">Parola Tekrar</label>
                    <input type="password" class="form-control <?php echo !empty($parolatkr_err) ? 'is-invalid' : ''; ?>" id="parolatkr" name="parolatkr" value="<?php echo isset($parolatkr) ? $parolatkr : ''; ?>">
                    <div class="invalid-feedback">
                        <?php echo $parolatkr_err; ?>
                    </div>
                </div>
                <button type="submit" name="kaydet" class="btn btn-primary">Kaydet</button>
                <a href="login.php" class="btn btn-secondary">Giriş Yap</a>

            </form>
        </div>
    </div>
    </body>
    </html>
