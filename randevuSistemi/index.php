<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoşgeldiniz</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #c8c7c7;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 100%; /* Resim genişliği ekran boyutuna göre ayarlanır */
            height: auto; /* Yükseklik orantılı olarak ayarlanır */
            max-width: 300px; /* Maksimum genişlik 300px */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .welcome-text {
            font-size: 2rem; /* Yazı boyutunu daha büyük yap */
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        .button-container {
            text-align: center;
        }

        .button-container button {
            padding: 12px 24px;
            font-size: 18px;
            color: white;
            background-color: #2d3e50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button-container button:hover {
            background-color: #6c757d;
        }

        /* Responsive Tasarım */
        @media screen and (max-width: 768px) {
            .welcome-text {
                font-size: 1.5rem; /* Mobilde yazı boyutunu küçült */
            }

            .button-container button {
                font-size: 16px; /* Buton fontunu küçült */
                padding: 10px 20px; /* Butonun içeriğini ayarla */
            }
        }

        @media screen and (max-width: 480px) {
            .welcome-text {
                font-size: 1.2rem; /* Küçük ekranlarda yazı boyutunu daha da küçült */
            }

            .button-container button {
                font-size: 14px; /* Buton fontunu daha da küçült */
                padding: 8px 16px; /* Butonun içeriğini daha da küçült */
            }
        }
    </style>
</head>
<body>
    <div class="logo">
        <p class="welcome-text">Hoşgeldiniz</p>
        <img src="img/loginpageee.jpg" alt="Hoşgeldiniz">
    </div>
    <div class="button-container">
        <button onclick="window.location.href='login.php';">Randevu Al</button>
    </div>
</body>
</html>
