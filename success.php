<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement Réussi</title>
    <link rel="stylesheet" href="assets/css/article.css">
    <style>
        body { text-align: center; padding-top: 50px; background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%); }
        .status-card { 
            background: #fff; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 8px 32px rgba(44, 62, 80, 0.13);
            display: inline-block;
            animation: fadeInCard 0.7s;
        }
        h1 { color: #27ae60; }
        @keyframes fadeInCard {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="status-card">
        <h1>Paiement Réussi !</h1>
        <p>Votre commande a été traitée avec succès.</p>
        <p>Vous pouvez vérifier le statut dans votre espace client.</p>
        <a href="article.php" class="acheter-btn" style="display: inline-block; text-decoration: none; padding: 12px 25px; margin-top: 20px; width: auto;">Retour aux articles</a>
    </div>
</body>
</html>