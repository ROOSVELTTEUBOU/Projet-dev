USE appecom;

-- Utilisateurs
INSERT INTO Utilisateur (nom, email, mot_de_passe, role) VALUES
('Amiral', 'amiral@appecom.com', 'hash_amiral', 'Administrateur'),
('Jean Employe', 'jean@appecom.com', 'hash_jean', 'Employe'),
('Marie Employe', 'marie@appecom.com', 'hash_marie', 'Employe'),
('Luc Employe', 'luc@appecom.com', 'hash_luc', 'Employe'),
('Sophie Employe', 'sophie@appecom.com', 'hash_sophie', 'Employe'),
('David Employe', 'david@appecom.com', 'hash_david', 'Employe'),
('Nathalie Employe', 'nathalie@appecom.com', 'hash_nathalie', 'Employe'),
('Eric Employe', 'eric@appecom.com', 'hash_eric', 'Employe'),
('Julie Employe', 'julie@appecom.com', 'hash_julie', 'Employe'),
('Marc Employe', 'marc@appecom.com', 'hash_marc', 'Employe');

-- Clients
INSERT INTO Client (nom, prenom, ville, sexe, contact, date_naissance, email, mot_de_passe) VALUES
('Ngue', 'Paul', 'Douala', 'M', '690112233', '1990-05-12', 'paul.client@appecom.com', 'hash_paul'),
('Kamga', 'Alice', 'Yaoundé', 'F', '691223344', '1988-09-20', 'alice.client@appecom.com', 'hash_alice'),
('Tchoua', 'Marc', 'Bafoussam', 'M', '692334455', '1995-02-15', 'marc.client@appecom.com', 'hash_marc'),
('Mbappe', 'Claire', 'Douala', 'F', '693445566', '1992-07-10', 'claire.client@appecom.com', 'hash_claire'),
('Fokou', 'Jean', 'Yaoundé', 'M', '694556677', '1985-03-22', 'jean.client@appecom.com', 'hash_jean'),
('Nana', 'Lucie', 'Bafoussam', 'F', '695667788', '1993-11-05', 'lucie.client@appecom.com', 'hash_lucie'),
('Talla', 'Roger', 'Douala', 'M', '696778899', '1991-01-18', 'roger.client@appecom.com', 'hash_roger'),
('Manga', 'Sylvie', 'Yaoundé', 'F', '697889900', '1989-06-30', 'sylvie.client@appecom.com', 'hash_sylvie'),
('Kouam', 'Daniel', 'Bafoussam', 'M', '698990011', '1994-04-12', 'daniel.client@appecom.com', 'hash_daniel'),
('Ngassa', 'Brigitte', 'Douala', 'F', '699001122', '1996-08-25', 'brigitte.client@appecom.com', 'hash_brigitte');

-- Fournisseurs
INSERT INTO Fournisseur (nom, adresse, ville, contact) VALUES
('ElectroCam', 'Marché Central', 'Douala', '670112233'),
('TechAfrique', 'Quartier Briqueterie', 'Yaoundé', '671223344'),
('GlobalElectro', 'Carrefour Tchitcha', 'Bafoussam', '672334455'),
('MegaTech', 'Bonapriso', 'Douala', '673445566'),
('InfoWorld', 'Mvog-Mbi', 'Yaoundé', '674556677'),
('CamElec', 'Marché A', 'Bafoussam', '675667788'),
('DigitalHouse', 'Akwa', 'Douala', '676778899'),
('SmartTech', 'Essos', 'Yaoundé', '677889900'),
('ElectroPlus', 'Marché B', 'Bafoussam', '678990011'),
('TechVision', 'Bonanjo', 'Douala', '679001122');

-- Familles
INSERT INTO Famille (libelle) VALUES
('Smartphones'),
('Ordinateurs'),
('Accessoires'),
('Tablettes'),
('Télévisions'),
('Imprimantes'),
('Réseaux'),
('Stockage'),
('Audio'),
('Composants');

-- Produits
INSERT INTO Produit (libelle, prix, description, quantite, id_famille) VALUES
('Casque Audio HD', 5000, 'Son immersif', 50, 9),
('Tablette TabMax', 40000, 'Mobilité et confort', 30, 4),
('Télévision 4K', 80000, 'Image ultra HD', 20, 5),
('Smartwatch Fit', 10000, 'Suivi santé', 40, 1),
('Caméra Pro', 65000, 'Qualité professionnelle', 15, 3),
('Imprimante Jet', 200000, 'Rapide et fiable', 10, 6),
('Routeur WiFi 6', 35000, 'Connexion rapide', 25, 7),
('Enceinte Bluetooth', 10000, 'Son puissant', 60, 9),
('Souris Gamer', 3000, 'Précision extrême', 100, 10),
('Clavier Mécanique', 5000, 'Confort et rapidité', 80, 10),
('Projecteur HD', 45000, 'Cinéma maison', 12, 5),
('Drone Cam', 20000, 'Vue aérienne', 18, 3),
('Console NextGen', 70000, 'Jeux immersifs', 22, 2),
('Micro Studio', 80000, 'Qualité audio', 14, 9),
('Webcam HD', 20000, 'Visio claire', 35, 3),
('Powerbank 20000mAh', 15000, 'Énergie portable', 50, 8),
('Casque VR', 15000, 'Réalité virtuelle', 20, 4),
('Assistant Vocal', 75000, 'Maison connectée', 25, 1);

-- Commandes Clients
INSERT INTO Commande_Client (date_commande, id_client, statut) VALUES
('2026-02-01', 1, 'Validée'),
('2026-02-02', 2, 'En attente'),
('2026-02-03', 3, 'Livrée'),
('2026-02-04', 4, 'Annulée'),
('2026-02-05', 5, 'Validée'),
('2026-02-06', 6, 'Livrée'),
('2026-02-07', 7, 'En attente'),
('2026-02-08', 8, 'Validée'),
('2026-02-09', 9, 'Livrée'),
('2026-02-10', 10, 'En attente');

-- Commandes Fournisseurs
INSERT INTO Commande_Fournisseur (date_commande, etat, id_fournisseur) VALUES
('2026-01-20', 'Reçue', 1),
('2026-01-21', 'En attente', 2),
('2026-01-22', 'Annulée', 3),
('2026-01-23', 'Reçue', 4),
('2026-01-24', 'En attente', 5),
('2026-01-25', 'Reçue', 6),
('2026-01-26', 'Annulée', 7),
('2026-01-27', 'Reçue', 8),
('2026-01-28', 'En attente', 9),
('2026-01-29', 'Reçue', 10);

-- Paiements
INSERT INTO Paiement (type, id_commande, montant, mode, date_paiement, statut) VALUES
('Client', 1, 1200000, 'Carte', '2026-02-01', 'Payé'),
('Client', 2, 450000, 'MobileMoney', '2026-02-02', 'En attente'),
('Client', 3, 300000, 'Cash', '2026-02-03', 'Payé'),
('Client', 4, 250000, 'Carte', '2026-02-04', 'Annulé'),
('Client', 5, 800000, 'Virement', '2026-02-05', 'Payé'),
('Client', 6, 600000, 'MobileMoney', '2026-02-06', 'Payé'),
('Client', 7, 150000, 'Cash', '2026-02-07', 'En attente'),
('Client', 8, 950000, 'Carte', '2026-02-08', 'Payé'),
('Client', 9, 700000, 'Virement', '2026-02-09', 'Payé'),
('Client', 10, 500000, 'MobileMoney', '2026-02-10', 'En attente');

-- Mouvements de stock
INSERT INTO Stock_Mouvement (id_produit, type, quantite) VALUES
(1, 'Entrée', 10),
(2, 'Sortie', 2),
(3, 'Entrée', 5),
(4, 'Sortie', 1),
(5, 'Entrée', 20),
(6, 'Sortie', 10),
(7, 'Entrée', 15),
(8, 'Sortie', 3),
(9, 'Entrée', 5),
(10, 'Sortie', 2);

-- Logs (journalisation des actions)
INSERT INTO Logs (id_utilisateur, action) VALUES
(1, 'Création de la commande client #1'),
(1, 'Validation de la commande fournisseur #1'),
(2, 'Ajout du produit Samsung Galaxy S23'),
(3, 'Suppression d’un client'),
(4, 'Mise à jour du stock Dell Inspiron'),
(5, 'Ajout d’un fournisseur TechVision'),
(6, 'Modification du rôle de Jean Employe'),
(7, 'Enregistrement du paiement #5'),
(8, 'Annulation de la commande client #4'),
(9, 'Exportation du rapport des ventes journalières');