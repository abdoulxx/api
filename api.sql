CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    prix DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255)
);

CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    ref_command VARCHAR(191) NOT NULL UNIQUE,
    status VARCHAR(20) NOT NULL DEFAULT 'en attente',
    payment_method VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id)
);
