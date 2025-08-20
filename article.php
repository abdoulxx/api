<?php
include('db.php');

$sql = "SELECT * FROM articles";
$stmt = $pdo->query($sql);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Articles</title>
    <link rel="stylesheet" href="assets/css/article.css">
</head>
<body>
    <h1 style="text-align: center;">Liste des Articles</h1>

<div class="container">
    <?php foreach ($articles as $article): ?>
    <div class="card">
        <img src="<?php echo $article['image_url']; ?>" alt="<?php echo $article['nom']; ?>">
        <div class="card-content">
            <h2><?php echo $article['nom']; ?></h2>
            <p><?php echo $article['description']; ?></p>
            <p class="price">Prix: <?php echo $article['prix']; ?> XOF</p>
            
            
            <form action="paiement.php" method="post">
                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                <input type="hidden" name="total_prices" value="<?php echo $article['prix']; ?>">
                <button type="submit" class="acheter-btn">Acheter</button>
            </form>
 
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="container" style="margin-top: 50px; justify-content: center;">
    <div style="width: 100%; max-width: 900px;">
        <h2 style="text-align: center; margin-bottom: 30px;">Historique des Commandes</h2>
        <table class="commands-table">
            <thead>
                <tr>
                    <th>ID Commande</th>
                    <th>Article</th>
                    <th>Status</th>
                    <th>Méthode de Paiement</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql_commandes = "SELECT c.ref_command, a.nom as article_nom, c.status, c.payment_method, c.created_at 
                                      FROM commandes c 
                                      JOIN articles a ON c.article_id = a.id 
                                      ORDER BY c.created_at DESC";
                    $stmt_commandes = $pdo->query($sql_commandes);
                    $commandes = $stmt_commandes->fetchAll(PDO::FETCH_ASSOC);

                    if (count($commandes) > 0) {
                        foreach ($commandes as $commande):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($commande['ref_command']); ?></td>
                    <td><?php echo htmlspecialchars($commande['article_nom']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo htmlspecialchars(strtolower($commande['status'])); ?>">
                            <?php echo htmlspecialchars(ucfirst($commande['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($commande['payment_method'] ? $commande['payment_method'] : 'N/A'); ?></td>
                    <td><?php echo date("d/m/Y H:i", strtotime($commande['created_at'])); ?></td>
                </tr>
                <?php 
                        endforeach;
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center;">Aucune commande pour le moment.</td></tr>';
                    }
                } catch (PDOException $e) {
                    echo '<tr><td colspan="5" style="text-align:center; color:red;">Erreur de chargement des commandes.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.commands-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(44, 62, 80, 0.13);
    overflow: hidden;
    animation: fadeInCard 0.7s;
}
.commands-table th, .commands-table td {
    padding: 15px 20px;
    text-align: left;
    border-bottom: 1px solid #dfe6e9;
}
.commands-table th {
    background-color: #f9f9f9;
    font-weight: 600;
    color: #2d3436;
}
.commands-table tr:last-child td {
    border-bottom: none;
}
.commands-table tr:hover {
    background-color: #f5f5f5;
}
.status-badge {
    padding: 5px 12px;
    border-radius: 15px;
    color: #fff;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: capitalize;
}
.status-paye { background-color: #27ae60; }
.status-en { background-color: #f39c12; } /* Pour 'en attente' */
.status-attente { background-color: #f39c12; } /* Pour 'en attente' */
.status-annule { background-color: #c0392b; }
</style>

</body>
</html>
