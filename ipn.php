<?php
include('db.php');

// Enregistre les données POST brutes pour le débogage
$raw_post_data = file_get_contents('php://input');
file_put_contents('ipn_log.txt', date('[Y-m-d H:i:s]') . "\n" . $raw_post_data . "\n\n", FILE_APPEND);

// Vous devriez ajouter une vérification de sécurité ici pour vous assurer que l'IPN provient bien de PayTech
// Par exemple, en vérifiant la signature ou l'adresse IP source

// Analyse les données URL-encodées provenant de PayTech
parse_str($raw_post_data, $ipn_data);

if (isset($ipn_data['ref_command'], $ipn_data['type_event'])) {
    $ref_command = $ipn_data['ref_command'];
    $type_event = $ipn_data['type_event']; // ex: 'sale_complete'
    $payment_method = $ipn_data['payment_method'] ?? '';

    // Traduit l'événement PayTech en statut pour notre application
    $new_status = ($type_event == 'sale_complete') ? 'paye' : 'annule';

    // Met à jour le statut de la commande dans la base de données
    try {
        $sql = "UPDATE commandes SET status = ?, payment_method = ? WHERE ref_command = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $payment_method, $ref_command]);

        // Répond à PayTech pour accuser réception
        http_response_code(200);
        echo "IPN Handled Successfully";

    } catch (PDOException $e) {
        // Enregistre les erreurs de base de données
        file_put_contents('ipn_log.txt', date('[Y-m-d H:i:s]') . " [DB_ERROR] " . $e->getMessage() . "\n", FILE_APPEND);
        http_response_code(500);
        echo "Database Error";
    }

} else {
    // Si les données ne sont pas valides, répond avec une erreur
    http_response_code(400);
    echo "Invalid IPN Data";
    
}
?>
