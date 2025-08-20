
<?php
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_size = $_FILES['image']['size'];
        $image_type = $_FILES['image']['type'];

        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
        if (!in_array($image_type, $allowed_types)) {
            $message = "<div class='alert error'>Erreur: Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.</div>";
        } else {
            $upload_dir = 'assets/uploads/';
            $image_path = $upload_dir . $image_name;
            if (!move_uploaded_file($image_tmp_name, $image_path)) {
                $message = "<div class='alert error'>Erreur lors du téléchargement du fichier.</div>";
            } else {
                try {
                    $pdo = new PDO('mysql:host=localhost;dbname=api', 'root', '');
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sql = "INSERT INTO articles (nom, description, prix, image_url) VALUES (?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nom, $description, $prix, $image_path]);
                    header('Location: article.php');
                    exit();
                } catch (Exception $e) {
                    $message = "<div class='alert error'>Erreur base de données: " . $e->getMessage() . "</div>";
                }
            }
        }
    } else {
        $message = "<div class='alert error'>Erreur: Veuillez sélectionner une image valide.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Article</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <div class="container">
        <h1>Ajouter un Article</h1>
        <?php if (!empty($message)) echo $message; ?>
        <form action="index.php" method="post" enctype="multipart/form-data" id="articleForm">
            <label for="nom">Nom de l'article :</label>
            <input type="text" name="nom" id="nom" required placeholder="Ex: Chaussure de sport">

            <label for="description">Description :</label>
            <textarea name="description" id="description" rows="4" required placeholder="Décrivez l'article..."></textarea>

            <label for="prix">Prix (cfa) :</label>
            <input type="number" name="prix" id="prix" required min="0" step="0.01" placeholder="Ex: 5000">

            <label for="image">Image :</label>
            <input type="file" name="image" id="image" accept="image/*" required>
            <div class="preview" id="preview"></div>

            <button type="submit">Ajouter Article</button>
        </form>
    </div>
    <script>
    // Prévisualisation de l'image
    document.getElementById('image').addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        preview.innerHTML = '';
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                const img = document.createElement('img');
                img.src = evt.target.result;
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>
