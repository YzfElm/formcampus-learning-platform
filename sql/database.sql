-- ============================================================
--  FormCampus — Base de données (XAMPP / MySQL)
--  ÉTAPE 1 : Importez ce fichier dans phpMyAdmin
--  ÉTAPE 2 : Ouvrez http://localhost/formcampus/install.php
-- ============================================================

DROP DATABASE IF EXISTS formcampus;
CREATE DATABASE formcampus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE formcampus;

CREATE TABLE formations (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(255)  NOT NULL,
    categorie   VARCHAR(100)  NOT NULL,
    description TEXT,
    duree       VARCHAR(50),
    prix        DECIMAL(10,2) NOT NULL DEFAULT 0,
    formateur   VARCHAR(150)  DEFAULT NULL,
    image       VARCHAR(255)  DEFAULT NULL,
    actif       TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    prenom     VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('USER','ADMIN') NOT NULL DEFAULT 'USER',
    statut     ENUM('ACTIF','SUSPENDU') NOT NULL DEFAULT 'ACTIF',
    avatar     VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE inscriptions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    formation_id INT NOT NULL,
    progression  INT NOT NULL DEFAULT 0,
    commentaire  TEXT,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_inscription (user_id, formation_id),
    FOREIGN KEY (user_id)      REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (formation_id) REFERENCES formations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    titre      VARCHAR(255) NOT NULL,
    message    TEXT NOT NULL,
    lu         TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT DEFAULT NULL,
    action     VARCHAR(100) NOT NULL,
    details    TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Comptes (mots de passe générés par install.php)
INSERT INTO users (nom, prenom, email, password, role) VALUES
('Campus',      'Admin', 'admin@formcampus.com', 'PLACEHOLDER', 'ADMIN'),
('Utilisateur', 'Demo',  'demo@formcampus.com',  'PLACEHOLDER', 'USER');

-- Formations de démonstration
INSERT INTO formations (titre, categorie, description, duree, prix, formateur) VALUES
('Developpement Web Fullstack', 'Informatique', 'Apprenez HTML, CSS, JavaScript, React et Node.js de zero a expert.', '3 mois', 1500.00, 'Jean Martin'),
('Initiation a Python',         'Informatique', 'Les bases de la programmation avec Python, NumPy et Pandas.', '4 semaines', 800.00, 'Sophie Durand'),
('Marketing Digital',           'Marketing',    'Reseaux sociaux, SEO, SEM et strategies de contenu.', '2 mois', 1200.00, 'Claire Leblanc'),
('Anglais Professionnel',       'Langues',      'Ameliorez votre anglais pour le monde du travail.', '6 mois', 900.00, 'James Wilson'),
('Design UX/UI',                'Design',       'Design centre utilisateur, Figma, wireframes et prototypes.', '2 mois', 1100.00, 'Camille Rousseau'),
('Cybersecurite Fondamentaux',  'Informatique', 'Securite informatique, cryptographie et ethical hacking.', '3 mois', 1800.00, 'Maxime Petit');
