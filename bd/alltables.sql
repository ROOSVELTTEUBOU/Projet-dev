-- Création de la base
CREATE DATABASE IF NOT EXISTS appecom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE appecom;

-- Table des utilisateurs
CREATE TABLE Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('Administrateur','Employe') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des clients
CREATE TABLE Client (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    ville VARCHAR(50),
    sexe CHAR(1),
    contact VARCHAR(20),
    date_naissance DATE,
    email VARCHAR(100) UNIQUE,
    mot_de_passe VARCHAR(255)
) ENGINE=InnoDB;

-- Table des fournisseurs
CREATE TABLE Fournisseur (
    id_fournisseur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    adresse VARCHAR(100),
    ville VARCHAR(50),
    contact VARCHAR(20)
) ENGINE=InnoDB;

-- Table des familles
CREATE TABLE Famille (
    id_famille INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Table des produits
CREATE TABLE Produit (
    id_produit INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL,
    quantite INT DEFAULT 0,
    id_famille INT,
    FOREIGN KEY (id_famille) REFERENCES Famille(id_famille)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Commandes clients
CREATE TABLE Commande_Client (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    date_commande DATE NOT NULL,
    id_client INT,
    statut ENUM('En attente','Validée','Livrée','Annulée') DEFAULT 'En attente',
    FOREIGN KEY (id_client) REFERENCES Client(id_client)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Commandes fournisseurs
CREATE TABLE Commande_Fournisseur (
    id_commande_f INT AUTO_INCREMENT PRIMARY KEY,
    date_commande DATE NOT NULL,
    etat ENUM('En attente','Reçue','Annulée') DEFAULT 'En attente',
    id_fournisseur INT,
    FOREIGN KEY (id_fournisseur) REFERENCES Fournisseur(id_fournisseur)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Liaison commande client <-> produit
CREATE TABLE Concerner_Client (
    id_commande INT,
    id_produit INT,
    quantite INT NOT NULL,
    PRIMARY KEY (id_commande, id_produit),
    FOREIGN KEY (id_commande) REFERENCES Commande_Client(id_commande)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES Produit(id_produit)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Liaison commande fournisseur <-> produit
CREATE TABLE Concerner_Fournisseur (
    id_commande_f INT,
    id_produit INT,
    quantite INT NOT NULL,
    PRIMARY KEY (id_commande_f, id_produit),
    FOREIGN KEY (id_commande_f) REFERENCES Commande_Fournisseur(id_commande_f)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES Produit(id_produit)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Paiements
CREATE TABLE Paiement (
    id_paiement INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('Client','Fournisseur') NOT NULL,
    id_commande INT,
    montant DECIMAL(10,2) NOT NULL,
    mode ENUM('Cash','Carte','MobileMoney','Virement'),
    date_paiement DATE NOT NULL,
    statut ENUM('Payé','En attente','Annulé') DEFAULT 'En attente'
) ENGINE=InnoDB;

-- Mouvements de stock
CREATE TABLE Stock_Mouvement (
    id_mouvement INT AUTO_INCREMENT PRIMARY KEY,
    id_produit INT,
    type ENUM('Entrée','Sortie') NOT NULL,
    quantite INT NOT NULL,
    date_mouvement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produit) REFERENCES Produit(id_produit)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Logs
CREATE TABLE Logs (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT,
    action VARCHAR(255) NOT NULL,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;