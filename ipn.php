<?php
include('db.php');

// Enregistre les données POST brutes pour le débogage
$raw_post_data = file_get_contents('php://input');
file_put_contents('ipn_log.txt', date('[Y-m-d H:i:s]') . "\n" . $raw_post_data . "\n\n", FILE_APPEND);



// Analyse les données URL-encodées provenant de PayTech
parse_str($raw_post_data, $ipn_data);

if (isset($ipn_data['ref_command'], $ipn_data['type_event'])) {
    $ref_command = $ipn_data['ref_command'];
    $type_event = $ipn_data['type_event']; // ex: 'sale_complete'
    $payment_method = $ipn_data['payment_method'] ?? '';

    $new_status = ($type_event == 'sale_complete') ? 'paye' : 'annule';

    try {
        $sql = "UPDATE commandes SET status = ?, payment_method = ? WHERE ref_command = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $payment_method, $ref_command]);

        // Répond à PayTech pour accuser réception
        http_response_code(200);
        echo "IPN Handled Successfully";

    } catch (PDOException $e) {
        file_put_contents('ipn_log.txt', date('[Y-m-d H:i:s]') . " [DB_ERROR] " . $e->getMessage() . "\n", FILE_APPEND);
        http_response_code(500);
        echo "Database Error";
    }

} else {
    http_response_code(400);
    echo "Invalid IPN Data";
    
}
?>
