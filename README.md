# Documentation d'Implémentation : API de Paiement PayTech

---

## 1. Introduction

### 1.1. Objectif du Projet
Ce document détaille l'architecture et les étapes d'implémentation d'une mini-application web de e-commerce. Le projet intègre l'API de paiement de **PayTech** (Sénégal) pour gérer des transactions financières de manière sécurisée et asynchrone, en se basant sur une architecture logicielle simple et des services web.

### 1.2. Fonctionnalités Clés
- Ajout et affichage d'articles.
- Lancement d'une demande de paiement via l'API PayTech.
- Gestion des retours de paiement (succès, annulation) via redirection.
- Traitement asynchrone des confirmations de paiement via un Webhook (IPN - Instant Payment Notification).
- Stockage et mise à jour des statuts de commande dans une base de données.
- Consultation de l'historique des commandes.

### 1.3. Architecture Technologique
- **Langage de script côté serveur :** PHP 8
- **Base de données :** MySQL
- **Serveur Web :** Apache (via WAMP)
- **API Externe :** PayTech Payment Gateway

---

## 2. Prérequis Techniques

### 2.1. Logiciels
- Un environnement de développement web local complet comme **WAMP** (pour Windows) ou **XAMPP** (multiplateforme).

### 2.2. Extensions PHP requises
Assurez-vous que les extensions suivantes sont activées dans votre configuration PHP (`php.ini`) :
- `pdo_mysql` : Pour la connexion à la base de données MySQL avec PDO.
- `curl` : Pour effectuer les requêtes HTTP vers l'API de PayTech.

### 2.3. Compte Développeur PayTech
- Un compte développeur PayTech est indispensable pour obtenir des **clés API** (`API_KEY` et `API_SECRET`) pour l'environnement de test.

### 2.4. Outil de test (Ngrok)
- Pour tester la fonctionnalité IPN sur un serveur local, un outil de tunneling comme **Ngrok** est nécessaire. Il permet d'exposer votre serveur local à Internet via une URL `https://` sécurisée, une exigence de l'API PayTech.

---

## 3. Guide d'Installation et de Configuration

### 3.1. Étape 1 : Récupération des Fichiers
- Placez l'ensemble des fichiers du projet dans le répertoire racine de votre serveur web (par exemple, `C:/wamp64/www/api/`).

### 3.2. Étape 2 : Création et Importation de la Base de Données
1.  Ouvrez votre interface de gestion de base de données (phpMyAdmin).
2.  Créez une nouvelle base de données, par exemple `api`.
3.  Sélectionnez cette base de données et allez dans l'onglet "SQL".
4.  Copiez le contenu du fichier `api.sql` et exécutez la requête pour créer les tables `articles` et `commandes`.

### 3.3. Étape 3 : Configuration des Variables d'Environnement
1.  À la racine du projet, créez un fichier nommé `.env`.
2.  Copiez-y le contenu suivant et remplacez les valeurs par vos propres informations :

    ```
    # Configuration de la base de données
    DB_HOST=localhost
    DB_NAME=api
    DB_USER=root
    DB_PASS=

    # Clés API PayTech (à remplacer par vos clés de test)
    API_KEY=VOTRE_API_KEY
    API_SECRET=VOTRE_API_SECRET
    ```

### 3.4. Étape 4 : Configuration du Tunnel de Test (Ngrok)
1.  Lancez Ngrok pour créer un tunnel vers le port de votre serveur local (généralement le port 80) : `ngrok http 80`.
2.  Copiez l'URL `https://` fournie par Ngrok.
3.  Ouvrez le fichier `paiement.php` et remplacez les URL de `success_url`, `ipn_url`, et `cancel_url` par votre URL Ngrok. Exemple :
    `"ipn_url" => "https://VOTRE_URL_NGROK/api/ipn.php"`

---

## 4. Description de l'Architecture

### 4.1. Structure des Fichiers du Projet
- `/` (racine) : Contient les scripts PHP principaux, la configuration et la documentation.
- `/assets/css/` : Contient les feuilles de style.
- `/uploads/` : Répertoire de destination pour les images des articles.

### 4.2. Schéma de la Base de Données
- **Table `articles` :** Stocke les informations sur les produits à vendre.
- **Table `commandes` :** Stocke chaque transaction, son article associé, sa référence unique et son statut (`en attente`, `paye`, `annule`).

### 4.3. Flux du Processus de Paiement
Le processus peut être schématisé comme suit :

1.  **Client -> Serveur :** Le client sélectionne un article (`article.php`) et clique sur "Acheter".
2.  **Serveur -> Base de Données :** `paiement.php` enregistre une nouvelle commande avec le statut `en attente`.
3.  **Serveur -> PayTech :** `paiement.php` envoie une requête de paiement à l'API PayTech avec les détails de la commande.
4.  **PayTech -> Client :** Le client est redirigé vers la plateforme de paiement de PayTech.
5.  **Client -> Serveur (Redirection) :** Selon l'action du client, il est redirigé vers `success.php` ou `cancel.php`.
6.  **PayTech -> Serveur (Webhook) :** En arrière-plan, PayTech envoie une notification IPN à `ipn.php` pour confirmer le statut final de la transaction.
7.  **Serveur -> Base de Données :** `ipn.php` met à jour le statut de la commande (`paye` ou `annule`) et le moyen de paiement.

### 4.4. Rôle des Fichiers Clés
- `paiement.php` : **Orchestrateur de la transaction.** Prépare la commande, l'insère en base de données et initie la communication avec PayTech.
- `ipn.php` : **Récepteur de confiance.** C'est le service d'écoute (webhook) qui reçoit la confirmation de paiement asynchrone, garantissant la mise à jour fiable du statut de la commande, indépendamment de la redirection du client.
- `cancel.php` : **Gestionnaire de l'annulation.** Met à jour le statut en base de données si l'utilisateur annule explicitement la transaction.
- `config.php` & `db.php` : **Socle de configuration.** Centralisent la gestion de la configuration et la connexion à la base de données pour l'ensemble de l'application.

---

## 5. Sécurité et Bonnes Pratiques

### 5.1. Utilisation des Variables d'Environnement
- Les informations sensibles (clés API, identifiants de base de données) sont stockées dans un fichier `.env` et ne sont pas versionnées (grâce au `.gitignore`), conformément aux meilleures pratiques de sécurité.

### 5.2. Prévention des Injections SQL
- L'utilisation de **PDO avec des requêtes préparées** protège l'application contre les attaques par injection SQL.

### 5.3. Traitement Fiable des IPN
- L'implémentation d'un script IPN dédié permet de décorréler la confirmation du paiement de la redirection du client, ce qui rend le système robuste aux interruptions de connexion côté client.

---

## 6. Conclusion et Pistes d'Amélioration

### 6.1. Résumé du Travail Accomplished
Ce projet démontre avec succès l'intégration d'une API de paiement tierce dans une application web PHP. Il met en œuvre un cycle de transaction complet, de la création de la commande à sa confirmation asynchrone, en suivant des pratiques de développement structurées et sécurisées.
