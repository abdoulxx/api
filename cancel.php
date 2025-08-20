<?php
include('db.php');

if (isset($_GET['ref_command'])) {
    $ref_command = $_GET['ref_command'];

    try {
        // Mettre à jour le statut de la commande à 'annule'
        // On vérifie aussi que le statut est bien 'en attente' pour éviter de modifier une commande déjà payée
        $sql = "UPDATE commandes SET status = 'annule' WHERE ref_command = ? AND status = 'en attente'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ref_command]);
    } catch (PDOException $e) {
        // En cas d'erreur de base de données, on peut l'ignorer ou la logger, mais on affiche quand même la page d'annulation.
        // Pour le moment, nous n'affichons rien à l'utilisateur en cas d'erreur ici.
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement Annulé</title>
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
        h1 { color: #c0392b; }
        @keyframes fadeInCard {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="status-card">
        <h1>Paiement Annulé</h1>
        <p>Le processus de paiement a été annulé.</p>
        <p>Votre commande n'a pas été finalisée. Vous ne serez pas débité.</p>
        <a href="article.php" class="acheter-btn" style="display: inline-block; text-decoration: none; padding: 12px 25px; margin-top: 20px; width: auto;">Retour aux articles</a>
    </div>
</body>
</html>