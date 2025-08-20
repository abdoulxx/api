<?php
include('db.php');

// Log the raw POST data for debugging
$raw_post_data = file_get_contents('php://input');
file_put_contents('ipn_log.txt', date('[Y-m-d H:i:s]') . "\n" . $raw_post_data . "\n\n", FILE_APPEND);

// You should add a security check here to verify the IPN is from PayTech
// For example, by checking the signature or the source IP address

// Parse the URL-encoded data from PayTech
parse_str($raw_post_data, $ipn_data);

if (isset($ipn_data['ref_command'], $ipn_data['type_event'])) {
    $ref_command = $ipn_data['ref_command'];
    $type_event = $ipn_data['type_event']; // e.g., 'sale_complete'
    $payment_method = $ipn_data['payment_method'] ?? '';

    // Translate PayTech event to our application status
    $new_status = ($type_event == 'sale_complete') ? 'paye' : 'annule';

    // Update the command status in the database
    try {
        $sql = "UPDATE commandes SET status = ?, payment_method = ? WHERE ref_command = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $payment_method, $ref_command]);

        // Respond to PayTech to acknowledge receipt
        http_response_code(200);
        echo "IPN Handled Successfully";

    } catch (PDOException $e) {
        // Log database errors
        file_put_contents('ipn_log.txt', date('[Y-m-d H:i:s]') . " [DB_ERROR] " . $e->getMessage() . "\n", FILE_APPEND);
        http_response_code(500);
        echo "Database Error";
    }

} else {
    // If the data is not valid, respond with an error
    http_response_code(400);
    echo "Invalid IPN Data";
    
}
?>
