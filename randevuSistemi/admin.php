<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include("baglanti.php"); // Veritabanı bağlantısını buradan dahil ediyoruz

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Randevu onaylama veya reddetme işlemi
if (isset($_POST['onayla'])) {
    $id = intval($_POST['id']); 
    $query = "UPDATE randevular SET randevu_durum='onaylandı' WHERE id=?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        if (sendMail($id, $_POST['kullanici_adi'], $_POST['email'], 'onaylandı')) {
            $_SESSION['admin_message'] = "Mail gönderildi ve admin bilgilendirildi.";
        } else {
            $_SESSION['admin_message'] = "Mail gönderilirken bir hata oluştu.";
        }
        header("Location: admin.php");
        exit();
    } else {
        die("Sorgu hatası: " . mysqli_error($conn));
    }
} elseif (isset($_POST['reddet'])) {
    
    $id = intval($_POST['id']);
    $query = "UPDATE randevular SET randevu_durum='reddedildi' WHERE id=?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        if (sendMail($id, $_POST['kullanici_adi'], $_POST['email'], 'reddedildi')) {
            $_SESSION['admin_message'] = "Mail gönderildi ve admin bilgilendirildi.";
        } else {
            $_SESSION['admin_message'] = "Mail gönderilirken bir hata oluştu.";
        }
        header("Location: admin.php");
        exit();
    } else {
        die("Sorgu hatası: " . mysqli_error($conn));
    }
}

// Kullanıcı kontrolü - yalnızca adminler erişebilir
if (!isset($_SESSION["user_typ"]) || $_SESSION["user_typ"] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Kullanıcı yönetimi işlemleri
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["sil"])) {
    $id = intval($_POST["id"]);
    $delete_query = "DELETE FROM kullanicilar WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['admin_message'] = "Kullanıcı başarıyla silindi!";
        } else {
            $_SESSION['admin_message'] = "Silme işlemi başarısız.";
        }
    } else {
        die("Sorgu hatası: " . mysqli_error($conn));
    }
}

// Kullanıcıları listeleme
$query = "SELECT id, kullanici_adi, email, user_typ FROM kullanicilar";
$result = mysqli_query($conn, $query);
function sendMail($id, $kullanici_adi) {
    global $conn;

    // Veritabanından randevu durumu almak
    $query = "SELECT randevu_durum FROM randevular WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $randevu_durum);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$randevu_durum) {
        die("Randevu durumu bulunamadı.");
    }

    // Kullanıcının e-posta adresini almak için sorgulama yapın
    $query = "SELECT email FROM kullanicilar WHERE kullanici_adi = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $kullanici_adi);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $email);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$email) {
        die("Kullanıcı bulunamadı.");
    }

    // PHPMailer örneği oluşturun
    $mail = new PHPMailer(true);
    try {
        // Sunucu ayarları
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'karlikaya0516@gmail.com'; // Kullanıcı Gmail hesabı
        $mail->Password   = 'zvwzcjhzzindcfwd'; // Gmail API veya şifre
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Gönderici bilgisi
        $mail->setFrom('karlikaya0516@gmail.com', 'mehmet karlikaya');
        $mail->addAddress($email, $kullanici_adi); // Yalnızca ilgili kullanıcıya e-posta gönder

        // E-posta içeriği
        $mail->isHTML(true);
        $mail->Subject = "Randevu Durumu: {$randevu_durum}";

        // Duruma göre e-posta içeriğini özelleştirin
        if ($randevu_durum == 'onaylandı') {
            $mail->Body = "Merhaba, {$kullanici_adi} e-posta adresine kayıtlı kullanıcı!<br><br>Randevunuz onaylandı.<br>Lütfen admin panelden detayları kontrol edin.";
            $mail->AltBody = "Merhaba, {$kullanici_adi}! Randevunuz onaylandı. Detayları kontrol edin.";
        } elseif ($randevu_durum == 'reddedildi') {
            $mail->Body = "Merhaba, {$kullanici_adi} e-posta adresine kayıtlı kullanıcı!<br><br>Randevunuz reddedildi.<br>Lütfen admin panelden detayları kontrol edin.";
            $mail->AltBody = "Merhaba, {$kullanici_adi}! Randevunuz reddedildi. Detayları kontrol edin.";
        } else {
            $mail->Body = "Merhaba, {$kullanici_adi} e-posta adresine kayıtlı kullanıcı!<br><br>Randevunuzun durumu: <strong>{$randevu_durum}</strong>.<br>Lütfen admin panelden detayları kontrol edin.";
            $mail->AltBody = "Merhaba, {$kullanici_adi}! Randevunuzun durumu: {$randevu_durum}. Detayları kontrol edin.";
        }

        // E-postayı gönder
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("E-posta gönderme hatası: " . $mail->ErrorInfo);
        return false;
    }
}


?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Kullanıcı Yönetimi ve Randevu Yönetimi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Kullanıcı Yönetimi</h2>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["sil"])) {
    $id = intval($_POST["id"]);
    $delete_query = "DELETE FROM kullanicilar WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['admin_message'] = "randevu başarıyla silindi!";
        } else {
            $_SESSION['admin_message'] = "Silme işlemi başarısız.";
        }
    } else {
        die("Sorgu hatası: " . mysqli_error($conn));
    }
}

?>

    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-info mt-3">
            <?= $_SESSION['admin_message'] ?>
        </div>
        <?php unset($_SESSION['admin_message']); ?>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Kullanıcı Adı</th>
            <th>E-posta</th>
            <th>Yetki</th>
            <th>Sil</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row["id"] ?></td>
                <td><?= $row["kullanici_adi"] ?></td>
                <td><?= $row["email"] ?></td>
                <td><?= $row["user_typ"] ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $row["id"] ?>">
                        <button type="submit" name="sil" class="btn btn-danger btn-sm">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="container mt-5">
    <h2>Randevu Yönetimi</h2>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kullanıcı Adı</th>
                <th>Saat</th>
                <th>Ad</th>
                <th>Email</th>
                <th>Numara</th>
                <th>Randevu Tarihi</th>
                <th>Randevu Saati</th>
                <th>Durum</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php
               $query = "SELECT id, kullanici_adi, saat, ad, numara, randevu_tarihi, randevu_saati, randevu_durum, email FROM randevular";
               $result = mysqli_query($conn, $query);
   
               while ($row = mysqli_fetch_assoc($result)) :
               ?>
               <tr>
                   <td><?= $row["id"] ?></td>
                   <td><?= $row["kullanici_adi"] ?></td>
                   <td><?= $row["saat"] ?></td>
                   <td><?= $row["ad"] ?></td>
                   <td><?= $row["email"] ?></td>
                   <td><?= $row["numara"] ?></td>
                   <td><?= $row["randevu_tarihi"] ?></td>
                   <td><?= $row["randevu_saati"] ?></td>

                   <td>
                       <?php
                       if ($row["randevu_durum"] == 'onay bekliyor') {
                           echo '<span class="text-warning">Onay bekliyor</span>';
                       } elseif ($row["randevu_durum"] == 'onaylandı') {
                           echo '<span class="text-success">Onaylandı</span>';
                       } elseif ($row["randevu_durum"] == 'reddedildi') {
                           echo '<span class="text-danger">Reddedildi</span>';
                       } else {
                           echo '<span class="text-muted">Durum belirsiz</span>';
                       }
                       ?>
                   </td>
                   <td>
                       <?php if ($row["randevu_durum"] == 'onay bekliyor') : ?>
                           <form method="POST" style="display:inline-block;">
                               <input type="hidden" name="id" value="<?= $row['id'] ?>">
                               <input type="hidden" name="kullanici_adi" value="<?= $row['kullanici_adi'] ?>">
                               <input type="hidden" name="email" value="<?= $row['email'] ?>">
                               <button type="submit" name="onayla" class="btn btn-success btn-sm">Onayla</button>
                           </form>
                           <form method="POST" style="display:inline-block;">
                               <input type="hidden" name="id" value="<?= $row['id'] ?>">
                               <input type="hidden" name="kullanici_adi" value="<?= $row['kullanici_adi'] ?>">
                               <input type="hidden" name="email" value="<?= $row['email'] ?>">
                               <button type="submit" name="reddet" class="btn btn-danger btn-sm">Reddet</button>
                           </form>
                       <?php else: ?>
                           -
                       <?php endif; ?>
                   </td>
               </tr>
               <?php endwhile; ?>
           </tbody>
       </table>
   </div>
   
   <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   </body>
   </html> 