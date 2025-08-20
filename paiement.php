<?php
include('config.php');
include('db.php');

/**
 * 
 * @param string $url 
 * @param array $data 
 * @param array $header 
 * @return string 
 */
 
function post($url, $data = [], $header = [])
{
    $strPostField = http_build_query($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $strPostField);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($header, [
        'Content-Type: application/x-www-form-urlencoded;charset=utf-8',
        'Content-Length: ' . mb_strlen($strPostField)
    ]));
    return curl_exec($ch);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $env = loadEnv(__DIR__ . '/.env');
    $api_key = $env['API_KEY'] ?? '';
    $api_secret = $env['API_SECRET'] ?? '';

    $article_id = $_POST['article_id'];
    $prix_total = $_POST['total_prices'];

    $sql = "SELECT * FROM articles WHERE id = :article_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['article_id' => $article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Article non trouvé dans la base de données.");
    }

    $ref_command = uniqid('CMD_');

    try {
        $sql_insert_cmd = "INSERT INTO commandes (article_id, ref_command, status) VALUES (?, ?, ?)";
        $stmt_insert_cmd = $pdo->prepare($sql_insert_cmd);
        $stmt_insert_cmd->execute([$article_id, $ref_command, 'en attente']);
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement de la commande : " . $e->getMessage());
    }

    $postFields = [ 
        "item_name"     => $article['nom'],
        "item_price"    => $prix_total,
        "currency"      => "XOF",
        "ref_command"   => $ref_command,
        "command_name"  => "Achat de " . $article['nom'],
        "env"           => "test", 
        "success_url"   => "https://fb18d7c4387d.ngrok-free.app/api/success.php", 
        "ipn_url"       => "https://fb18d7c4387d.ngrok-free.app/api/ipn.php",       
        "cancel_url"    => "https://fb18d7c4387d.ngrok-free.app/api/cancel.php?ref_command=" . $ref_command,
    ];

    $jsonResponse = post('https://paytech.sn/api/payment/request-payment', $postFields, [
        "API_KEY: $api_key", 
        "API_SECRET: $api_secret"
    ]);

    $response = json_decode($jsonResponse, true); 

    if (isset($response['success']) && $response['success'] == 1) {

        header('Location: ' . $response['redirect_url']);
        exit();

    } else {
        echo "<h1>Erreur lors de la demande de paiement</h1>";
        echo "<p>La réponse de l'API de paiement n'a pas indiqué de succès.</p>";
        echo "<h3>Détails de la réponse :</h3>";
        echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>";
        print_r($response);
        echo "</pre>";
    }
}
