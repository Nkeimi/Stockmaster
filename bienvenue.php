<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue - StockMaster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            /* On utilise un dégradé pro en attendant ton img.jpg */
            background: linear-gradient(rgba(2, 132, 199, 0.8), rgba(2, 4, 39, 0.9)), url('img.jpg');
            background-size: cover;
            background-position: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            overflow: hidden;
        }

        .welcome-container {
            text-align: center;
            animation: fadeIn 1.5s ease;
        }

        h1 {
            font-size: 3.5rem;
            margin-bottom: 10px;
            letter-spacing: 2px;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
        }

        p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .btn-commencer {
            background-color: #0284c7; /* Ton bleu StockMaster */
            color: #fff;
            padding: 18px 50px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px; /* Coins plus arrondis pour un look moderne */
            border: 2px solid white;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-commencer:hover {
            background-color: white;
            color: #0284c7;
            transform: scale(1.1) translateY(-5px);
            box-shadow: 0px 15px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-commencer:active {
            transform: scale(0.95);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="welcome-container">
        <i class="fas fa-boxes" style="font-size: 80px; margin-bottom: 20px;"></i>
        
        <h1>StockMaster</h1>
        <p>Gérez votre inventaire avec précision et simplicité.</p>

        <form action="index.php">
            <button class="btn-commencer" type="submit">
                Commencer <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
            </button>
        </form>
    </div>

</body>
</html>