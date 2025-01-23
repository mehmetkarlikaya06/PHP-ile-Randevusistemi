<?php
session_start();

// Veritabanı bağlantısı
$host = "localhost";
$kullanici = "u0914930_memoliadmin";
$parola = "DM=8KPVZ{gNc";
$vt = "u0914930_uyelik2";

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

// Kullanıcının randevularını getir
$stmt = $db->prepare("SELECT id, randevu_tarihi, randevu_saati, ad, numara, randevu_durum FROM randevular WHERE kullanici_adi = :kullanici_adi ORDER BY randevu_tarihi ASC, randevu_saati ASC");
$stmt->execute([':kullanici_adi' => $_SESSION['usurname']]);
$randevular = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevularım</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #c8c7c7;
        }

        .header {
            background-color: #2d3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            margin-top: 20px;
        }

        table th, table td {
            vertical-align: middle;
        }

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
        }

        .table th {
            background-color: #add7d8;
            color: white;
        }

        .btn-custom {
            background-color: #2d3e50;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
    .header {
        padding: 15px;
        font-size: 18px;
    }
    
    .container {
        margin-top: 10px;
    }

    .table-container {
        padding: 15px;
    }

    table {
        width: 100%;
        display: block; /* Alt alta görünmesi için */
    }

    table th, table td {
        width: 100%; /* Sütunları mobilde alt alta yerleştirir */
        box-sizing: border-box;
        padding: 10px;
        text-align: left;
        display: flex;
        flex-direction: column; /* Sütunları üst üste sıralar */
    }

    table th {
        display: flex;
        justify-content: space-between; /* Karşılıklı değerler */
    }

    .btn-custom {
        width: 100%;
        padding: 10px;
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .header {
        font-size: 16px;
        padding: 10px;
    }

    .btn-custom {
        font-size: 14px;
    }

    table th, table td {
        width: 100%; /* Sütunları mobilde alt alta yerleştirir */
        padding: 10px 0;
    }

    table th {
        display: flex;
        justify-content: space-between; /* Karşılıklı değerler */
    }
}

@media (max-width: 360px) {
    .header {
        font-size: 14px;
        padding: 8px;
    }

    .btn-custom {
        font-size: 12px;
        padding: 8px;
    }

    table th, table td {
        padding: 8px 0;
        width: 100%; /* Tam ekran genişliği için */
    }

    table th {
        display: flex;
        justify-content: space-between; /* Karşılıklı değerler */
    }
}

@media (max-width: 320px) {
    .header {
        font-size: 12px;
        padding: 6px;
    }

    .btn-custom {
        font-size: 10px;
        padding: 6px;
    }

    table th, table td {
        padding: 6px 0;
    }

    table th {
        display: flex;
        justify-content: space-between; /* Karşılıklı değerler */
    }
}

        
    </style>
</head>
<body>

    <div class="header">
        <h1>Randevularım</h1>
    </div>

    <div class="container mt-5">
        <div class="table-container">
            <?php if (count($randevular) > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Randevu Saati</th>
                            <th>Ad</th>
                            <th>Telefon</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($randevular as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['randevu_tarihi']); ?></td>
                                <td><?= htmlspecialchars($row['randevu_saati']); ?></td>
                                <td><?= htmlspecialchars($row['ad']); ?></td>
                                <td><?= htmlspecialchars($row['numara']); ?></td>
                                <td><?= htmlspecialchars($row['randevu_durum']); ?></td>
                                <td>
                                    <a href="profile.php" class="btn btn-custom btn-sm"> Geri Dön</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    Henüz randevunuz yok.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center mt-5">
        <p>&copy; 2025 Randevu Sistemi. Tüm hakları saklıdır.</p>
    </footer>

</body>
</html>
