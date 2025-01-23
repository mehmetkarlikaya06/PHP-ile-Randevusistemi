<?php
session_start();

// Veritabanı bağlantısı
$host = "localhost";
$kullanici = "u0914930_memoliadmin";
$parola = "DM=8KPVZ{gNc";
$vt = "u0914930_uyelik2";
// Oturum açık değilse giriş sayfasına yönlendir
if (!isset($_SESSION["usurname"]) || $_SESSION["verified"] != 1) {
    header("Location: login.php");
    exit();
}

try {
    $db = new PDO("mysql:host=$host;dbname=$vt;charset=utf8", $kullanici, $parola);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Veritabanı bağlantısı başarısız: " . $e->getMessage();
    exit;
}

// Oturum kontrolü
if (!isset($_SESSION["usurname"])) {
    echo "Lütfen önce giriş yapın.";
    exit;
}




// Randevu silme işlemi
if (isset($_GET['sil']) && isset($_GET['randevu_id'])) {
    $randevuId = $_GET['randevu_id'];
    $stmt = $db->prepare("DELETE FROM randevular WHERE id = :id AND kullanici_adi = :kullanici_adi");
    $stmt->execute([':id' => $randevuId, ':kullanici_adi' => $_SESSION['usurname']]);
    if ($stmt->rowCount() > 0) {
        echo "<p class='text-success'>Randevu başarıyla silindi.</p>";
    } else {
        // echo "<p class='text-danger'>Randevu silinemedi veya size ait değil.</p>";
    }
}

// Tarih aralığı
$bugun = new DateTime();
$ucGunSonra = new DateTime();
$ucGunSonra->modify('+30 days');

$randevuVarMiMesaji = "";
$randevuBasariMesaji = "";

// Randevu alma işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['randevu_saati'], $_POST['randevu_tarihi'], $_POST['ad'], $_POST['numara'])) {
    $randevuTarihi = $_POST['randevu_tarihi'];
    $randevuSaati = $_POST['randevu_saati'];
    $ad = $_POST['ad'];
    $numara = $_POST['numara'];

    // Aynı tarihte ve saatte randevu kontrolü
    $stmt = $db->prepare("SELECT id FROM randevular WHERE randevu_tarihi = :randevu_tarihi AND randevu_saati = :randevu_saati");
    $stmt->execute([':randevu_tarihi' => $randevuTarihi, ':randevu_saati' => $randevuSaati]);
    $randevuVarMi = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($randevuVarMi) {
        $randevuVarMiMesaji = "Bu tarihte ve saatte başka bir randevu mevcut. Lütfen başka bir saat seçiniz.";
    } else {
        // Randevu ekleme
        $stmt = $db->prepare("INSERT INTO randevular (kullanici_adi, randevu_tarihi, randevu_saati, ad, numara, randevu_durum) VALUES (:kullanici_adi, :randevu_tarihi, :randevu_saati, :ad, :numara, 'Onay Bekliyor')");
        $stmt->execute([
            ':kullanici_adi' => $_SESSION['usurname'],
            ':randevu_tarihi' => $randevuTarihi,
            ':randevu_saati' => $randevuSaati,
            ':ad' => $ad,
            ':numara' => $numara
        ]);
        $randevuBasariMesaji = "Randevunuz başarıyla alındı.";
    }
}

// Kullanıcının randevularını getir
// $stmt = $db->prepare("SELECT id, randevu_tarihi, randevu_saati, ad, numara, randevu_durum FROM randevular WHERE kullanici_adi = :kullanici_adi ORDER BY randevu_tarihi ASC, randevu_saati ASC");
// $stmt->execute([':kullanici_adi' => $_SESSION['usurname']]);
// $randevular = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Saatler
$saatler = ['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00',  '19:00',  '20:00',  '21:00',  '22:00'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <a href="cikis.php" class="btn btncikis">Çıkış Yap</a>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    text-align: center;
    background-color: #c8c7c7;    
}

.header {
    background-color: #2d3e50;
    color: #fff;
    padding: 20px;
    text-align: center;
    margin-bottom: 20px;
}

.form-control {
    display: block;
    width: 85%;
    margin: 0 auto 20px auto;
    height: calc(1.5em + .75rem + 2px);
    padding: .375rem .75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}

.btn-custom {
    background-color: #6c757d;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border-radius: .25rem;
    transition: background-color .2s ease-in-out;
}
.text-danger {
    color: #dc3545 !important;
    top: 78%;
    position: relative;
}

.btn-custom:hover {
    background-color: #0056b3;
}
.btncikis {
    background-color: #6c757d;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 14px;
    border-radius: .25rem;
    transition: background-color .2s ease-in-out;
    position: relative;
    top: 411px;
}

.table-container {
    background: #ffffff;
    box-shadow: -12px 13px 12px 0px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    padding: 40px;
    background-color: #add7d8;
    margin: 20px auto;
    width: 90%;
    max-width: 1200px;
}

.alert-success {
    color: #155724;
    background-color: #00fd3d;
    border-color: #00fd3d;
    position: relative;
    top: 30px;
}

.alert-danger {
    color: #000000;
    background-color: #ff1c31;
    border-color: #ff1c31;
    position: relative;
    top: 30px;

}

footer {
    width: 100%;
    text-align: center;
    padding: 10px;
    background-color: #343a40;
    color: #fff;
}
.table-bordered {
    border: 1px solid #dee2e6;
    width: 100%;
    border-collapse: collapse;
}

.table-bordered th,
.table-bordered td {
    border: 1px solid #dee2e6;
    padding: 0.75rem;
    text-align: left;
    vertical-align: top;
}

.table-bordered thead th {
    background-color: #f8f9fa;
    font-weight: bold;
}

/* Responsive Kısımlar */
@media (min-width: 1024) {
    .alert-danger {
    color: #000000;
    background-color: #ff1c31;
    border-color: #ff1c31;
    position: relative;
    top: 60px;
}
.btn-custom {
    background-color: #6c757d;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border-radius: .25rem;
    transition: background-color .2s ease-in-out;
    position: relative;
    top: -8px;
}
.alert-success {
    color: #155724;
    background-color: #00fd3d;
    border-color: #00fd3d;
    position: relative;
    top: 54px;
}
.btncikis {
    background-color: #6c757d;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 12px;
    border-radius: .25rem;
    transition: background-color .2s ease-in-out;
    position: relative;
    top: 191px;
    left: 33%;
}
}
@media (max-width: 992px) {
    .form-control {
        width: 90%;
        font-size: 0.95rem;
    }

    .table-container {
        padding: 30px;
    }

    .header {
        font-size: 1.5rem;
        padding: 15px;
    }
    .table-bordered {
        font-size: 0.95rem;
    }
}

@media (max-width: 768px) {
    .form-control {
        font-size: 0.9rem;
        height: calc(1.4em + .6rem + 2px);
    }
    .alert-danger {
    color: #000000;
    background-color: #ff1c31;
    border-color: #ff1c31;
    position: relative;
    top: 56px;
    width: 570px;
    height: 48px;
}

.btncikis {
    background-color: #6c757d;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 14px;
    border-radius: .25rem;
    transition: background-color .2s ease-in-out;
    position: relative;
    top: 206px;
    left: 126px;
}
    .btn-custom {
        font-size: 0.9rem;
        padding: 0.5rem;
    }

    .header {
        font-size: 1.2rem;
    }

    .table-container {
        padding: 20px;
    }

    footer {
        font-size: 0.9rem;
    }
    .table-bordered {
        font-size: 0.9rem;
    }

    .table-container {
        padding: 15px;
    }

    .table-bordered th, .table-bordered td {
        display: block;
        width: 100%;
        box-sizing: border-box;
        padding: 0.6rem;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .table-bordered th::after {
        content: ':';
        display: inline-block;
        width: 100px;
    }

    .table-bordered td::before {
        content: attr(data-label);
        font-weight: bold;
        display: inline-block;
        width: 100px;
    }
}

@media (max-width: 576px) {
    .form-control {
        font-size: 0.85rem;
        height: calc(1.2em + .5rem + 2px);
    }

    .btn-custom {
        font-size: 0.8rem;
        padding: 0.4rem;
    }

    .header {
        font-size: 1rem;
    }

    .table-container {
        padding: 15px;
    }

    footer {
        font-size: 0.8rem;
    }
    .table-bordered {
        font-size: 0.85rem;
    }

    .table-bordered th,
    .table-bordered td {
        padding: 0.5rem;
    }
}
@media (max-width: 576px) {
    .alert-danger {
    color: #000000;
    background-color: #ff1c31;
    border-color: #ff1c31;
    position: relative;
    top: 30px;
    width: 329px;
    height: 68px;
}
.btncikis {
    background-color: #6c757d;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 14px;
    border-radius: .25rem;
    transition: background-color .2s ease-in-out;
    position: relative;
    top: 206px;
    left: 126px;
}
.btn-custom {
        font-size: 0.8rem;
        padding: 0.4rem;
        position: relative;
        top: -7px;
    }
}

@media (max-width: 360px) {
    .form-control {
        font-size: 0.75rem;
        height: calc(1.2em + 0.5rem + 2px);
        width: 90%;
    }

    .btn-custom {
        font-size: 0.75rem;
        padding: 0.4rem;
    }

    .header {
        font-size: 0.9rem;
    }

    footer {
        font-size: 0.75rem;
    }

    .table-container {
        padding: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .table-bordered th, .table-bordered td {
        width: calc(33.33% - 10px); /* %33 genişlik, flexbox ile yan yana sıralama */
        box-sizing: border-box;
        padding: 0.3rem;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .table-bordered th {
        font-size: 0.75rem; /* Tablo başlıklarının font boyutunu küçült */
        padding-bottom: 0.3rem; /* Altına bilgi gelecek şekilde boşluk bırak */
    }

    .table-bordered td {
        font-size: 0.7rem;
        padding-top: 0.3rem;
    }

    .responsive-list {
        width: 100%;
    }

    .responsive-list-item {
        font-size: 0.75rem;
        margin-bottom: 5px;
    }

    .btn-danger {
        padding: 0.35rem 0.6rem;
    }
}

@media (max-width: 320px) {
    .form-control {
        font-size: 0.7rem;
        height: calc(1.2em + 0.4rem + 2px);
        width: 88%;
    }

    .btn-custom {
        font-size: 0.7rem;
        padding: 0.35rem;
    }

    .header {
        font-size: 0.8rem;
    }

    footer {
        font-size: 0.7rem;
    }

    .table-container {
        padding: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .table-bordered th, .table-bordered td {
        width: calc(33.33% - 10px); /* %33 genişlik, flexbox ile yan yana sıralama */
        box-sizing: border-box;
        padding: 0.3rem;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .table-bordered th {
        font-size: 0.7rem; /* Tablo başlıklarının font boyutunu küçült */
        padding-bottom: 0.3rem; /* Altına bilgi gelecek şekilde boşluk bırak */
    }

    .table-bordered td {
        font-size: 0.7rem;
        padding-top: 0.3rem;
    }

    .responsive-list {
        width: 100%;
    }

    .responsive-list-item {
        font-size: 0.7rem;
        margin-bottom: 5px;
    }

    .btn-danger {
        padding: 0.35rem 0.5rem;
    }
}
    </style>
</head>
<body>
    <div class="header">
        <h1>Randevu Sistemine Hoş Geldiniz</h1>
    </div>

    <div class="container">
        <div class="table-container">
            <h2>Profil</h2>
            <p>Hoş geldiniz, <strong><?= htmlspecialchars($_SESSION["usurname"]); ?></strong></p>

            <div class="mt-5">
    <h3>Yeni Randevu Al</h3>
    <form method="post">
        <!-- Randevu Formu Alanları -->
    </form>

    <a href="randevu.php" class="btn btn-primary mt-3" >Randevularımı Görüntüle</a>
</div>
            <?php if ($randevuVarMiMesaji): ?>
                <div class="alert alert-danger"> <?= $randevuVarMiMesaji; ?> </div>
            <?php elseif ($randevuBasariMesaji): ?>
                <div class="alert alert-success"> <?= $randevuBasariMesaji; ?> </div>
            <?php endif; ?>

            <table class="table table-bordered">
                <thead>
                    <!-- <tr>
                        <th>Tarih</th>
                        <th>Randevu Saati</th>
                        <th>Ad</th>
                        <th>Telefon</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr> -->
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            <h3  >Yeni Randevu Al</h3>
            <form method="post">
                <div class="form-group">
                    <label for="randevu_tarihi">Tarih:</label>
                    <input type="date" id="randevu_tarihi" name="randevu_tarihi" min="<?= $bugun->format('Y-m-d'); ?>" max="<?= $ucGunSonra->format('Y-m-d'); ?>" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="randevu_saati">Saat:</label>
                    <select name="randevu_saati" id="randevu_saati" class="form-control" required>
                        <?php foreach ($saatler as $saat): ?>
                            <option value="<?= $saat; ?>"> <?= $saat; ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ad">Adınız:</label>
                    <input type="text" id="ad" name="ad" class="form-control"  required pattern="[A-Za-z\s]+" title="Sadece harf ve boşluk karakterleri kullanılabilir">
                </div>
                <div class="form-group">
                    <label for="numara">Telefon Numaranız:</label>
                    <input type="text" id="numara" name="numara" class="form-control" required pattern="^[0-9]{10}$" title="Geçerli bir Türkiye telefon numarası giriniz (örn. 5301234567)">
                    </div>
                <button type="submit" class="btn btn-custom">Randevu Al</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Randevu Sistemi. Tüm hakları saklıdır.</p>
    </footer>
  
    
</body>
</html>
