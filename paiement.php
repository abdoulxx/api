<?php
// Inclusion des fichiers de configuration et de connexion à la base de données.
include('config.php');
include('db.php');

/**
 * Fonction utilitaire pour effectuer des requêtes POST avec cURL.
 * @param string $url L'URL de l'API.
 * @param array $data Le tableau de données à envoyer.
 * @param array $header Le tableau des en-têtes HTTP.
 * @return string La réponse de l'API.
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

// Vérifie si la requête est une requête POST pour lancer le processus de paiement.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- Étape 1: Chargement de la configuration ---
    $env = loadEnv(__DIR__ . '/.env');
    $api_key = $env['API_KEY'] ?? '';
    $api_secret = $env['API_SECRET'] ?? '';

    // --- Étape 2: Récupération des données du formulaire ---
    $article_id = $_POST['article_id'];
    $prix_total = $_POST['total_prices'];

    // --- Étape 3: Récupération des informations de l'article depuis la base de données ---
    $sql = "SELECT * FROM articles WHERE id = :article_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['article_id' => $article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Article non trouvé dans la base de données.");
    }

    // --- Étape 4: Création de la commande dans notre système ---
    // On génère une référence unique pour cette commande.
    $ref_command = uniqid('CMD_');

    // On insère la commande dans la table `commandes` avec le statut 'en attente'.
    try {
        $sql_insert_cmd = "INSERT INTO commandes (article_id, ref_command, status) VALUES (?, ?, ?)";
        $stmt_insert_cmd = $pdo->prepare($sql_insert_cmd);
        $stmt_insert_cmd->execute([$article_id, $ref_command, 'en attente']);
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement de la commande : " . $e->getMessage());
    }

    // --- Étape 5: Préparation de la requête pour l'API PayTech ---
    $postFields = [
        "item_name"     => $article['nom'],
        "item_price"    => $prix_total,
        "currency"      => "XOF",
        "ref_command"   => $ref_command, // On utilise notre référence unique.
        "command_name"  => "Achat de " . $article['nom'],
        "env"           => "test", // Environnement de test.
        "success_url"   => "https://fb18d7c4387d.ngrok-free.app/api/success.php", 
        "ipn_url"       => "https://fb18d7c4387d.ngrok-free.app/api/ipn.php",       
        "cancel_url"    => "https://fb18d7c4387d.ngrok-free.app/api/cancel.php?ref_command=" . $ref_command,
    ];

    // --- Étape 6: Envoi de la requête à PayTech ---
    $jsonResponse = post('https://paytech.sn/api/payment/request-payment', $postFields, [
        "API_KEY: $api_key",
        "API_SECRET: $api_secret"
    ]);

    // --- Étape 7: Traitement de la réponse de PayTech ---
    $response = json_decode($jsonResponse, true);

    // Si la requête a réussi, PayTech renvoie une URL de redirection.
    if (isset($response['success']) && $response['success'] == 1) {
        // On redirige le client vers la page de paiement de PayTech.
        header('Location: ' . $response['redirect_url']);
        exit();
    } else {
        // En cas d'échec, on affiche un message d'erreur détaillé pour le débogage.
        echo "<h1>Erreur lors de la demande de paiement</h1>";
        echo "<p>La réponse de l'API de paiement n'a pas indiqué de succès.</p>";
        echo "<h3>Détails de la réponse :</h3>";
        echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>";
        print_r($response);
        echo "</pre>";
    }
}
