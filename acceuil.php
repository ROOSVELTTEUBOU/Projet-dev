<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Accueil - AMIRAL Electro. Coorp.</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f0f8ff;
            color: #003366;
        }

        .slogan {
            font-style: italic;
            font-size: 1.2em;
            margin-top: 10px;
        }

        nav {
            background-color: #007bff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            /* Gauche = modules, Droite = logout */
            align-items: center;
            flex-wrap: wrap;
            /* Permet de passer en ligne suivante si écran petit */
        }

        nav select {
            background-color: #0056b3;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            margin-right: 15px;
            width: 180px;
            text-align: center;
        }

        .logout-btn {
            background-color: #dc3545;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }

        /* Responsive : sur écran < 768px, le bouton passe en dessous */
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                /* Les éléments s’empilent */
                align-items: stretch;
            }

            .nav-left {
                display: flex;
                flex-direction: column;
                margin-bottom: 15px;
                gap: 15px;
            }

            .nav-right {
                margin-top: 10px;
                text-align: center;
                width: 100%;
                /* Prend toute la largeur pour centrer le bouton */
            }

            .logout-btn {
                width: 100%;
                /* Bouton prend toute la largeur */
            }
        }

        nav select option {
            background-color: rgb(114, 167, 247);
            /* Fond blanc pour les options */
            color: #000000;
            /* Texte noir pour lisibilité */
        }

        main {
            padding: 40px;
            text-align: center;
        }

        .products {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .card {
            background: #fff;
            border: 1px solid #007bff;
            border-radius: 8px;
            width: 220px;
            margin: 15px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card img {
            width: 220px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }

        .card h3 {
            margin: 10px 0;
        }

        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background: #0055aa;
        }

        .prix {
            color: white;
            background-color: rgb(0, 160, 21);
            font-weight: bold;
            font-size: 20px;
        }

        header {
            background: linear-gradient(90deg, #0055aa, #007bff);
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 2.5em;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        header h1 span {
            color: #ffd700;
        }

        .slogan {
            font-style: italic;
            font-size: 1.2em;
            margin-top: 10px;
        }

        footer {
            margin-top: 40px;
            background: #0055aa;
            color: #fff;
            text-align: center;
            padding: 10px;
        }
    </style>
</head>

<body>
    <header>
        <h1><span>AMIRAL</span> Electro. Coorp.</h1>
        <p class="slogan">Votre boutique en ligne de produits électroniques</p>
    </header>

    <nav>
        <!-- Module 1 : Gestion des entités -->
        <div class="nav-left">
            <select onchange="window.location.href=this.value">
                <option value="">👥 Gestion des entités</option>
                <option value="produits.php">📦 Produits</option>
                <option value="familles.php">🧬 Familles</option>
                <option value="clients.php">🧑‍💼 Clients</option>
                <option value="fournisseurs.php">🏢 Fournisseurs</option>
                <option value="utilisateurs.php">🔐 Utilisateurs</option>
            </select>

            <!-- Module 2 : Gestion des commandes -->
            <select onchange="window.location.href=this.value">
                <option value="">📋 Commandes</option>
                <option value="commande_client.php">🛒 Commande Client</option>
                <option value="commande_fournisseur.php">🚚 Commande Fournisseur</option>
                <option value="approvisionnement.php">🔄 Approvisionnement</option>
            </select>

            <!-- Module 3 : Gestion des paiements -->
            <select onchange="window.location.href=this.value">
                <option value="">💳 Paiements</option>
                <option value="paiement_client.php">👛 Paiement Client</option>
                <option value="paiement_fournisseur.php">💼 Paiement Fournisseur</option>
            </select>

            <!-- Module 4 : Documents et états -->
            <select onchange="window.location.href=this.value">
                <option value="">📄 Documents & États</option>
                <option value="bon_commande.php">📝 Bon de commande</option>
                <option value="bon_livraison.php">📦 Bon de livraison</option>
                <option value="bon_reception.php">📥 Bon de réception</option>
                <option value="facture_client.php">🧾 Facture Client</option>
                <option value="facture_fournisseur.php">📃 Facture Fournisseur</option>
            </select>

            <!-- Module 5 : Administration -->
            <select onchange="window.location.href=this.value">
                <option value="">🛠️ Administration</option>
                <option value="roles.php">🔧 Rôles</option>
                <option value="droits.php">🛡️ Droits</option>
                <option value="logs.php">📚 Journalisation</option>
            </select>

            <!-- Module 7 : Reporting et statistiques -->
            <select onchange="window.location.href=this.value">
                <option value="">📈 Reporting</option>
                <option value="ventes_journalieres.php">📉 Ventes journalières</option>
                <option value="stats_produits.php">📊 Produits les plus vendus</option>
                <option value="stats_clients.php">👥 Clients actifs</option>
            </select>
        </div>
        <div class="nav-right">
            <a href="logout.php" class="logout-btn">🚪 Logout</a>
        </div>


    </nav>

    <main>
        <div class="products">
            <!-- Exemple de 20 produits -->
            <div class="card"><img src="images/smartphone.jpg" alt="Smartphone">
                <h3>Smartphone X200</h3>
                <p>Performance et élégance</p>
                <p class="prix">50 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/laptop.jpg" alt="Laptop">
                <h3>Ordinateur ProBook</h3>
                <p>Puissance pour vos projets</p>
                <p class="prix">200 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/headphones.jpg" alt="Casque">
                <h3>Casque Audio HD</h3>
                <p>Son immersif</p>
                <p class="prix">5 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/tablette.jpg" alt="Tablette">
                <h3>Tablette TabMax</h3>
                <p>Mobilité et confort</p>
                <p class="prix">40 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/tv.jpg" alt="TV">
                <h3>Télévision 4K</h3>
                <p>Image ultra HD</p>
                <p class="prix">80 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/smartwatch.jpg" alt="Montre">
                <h3>Smartwatch Fit</h3>
                <p>Suivi santé</p>
                <p class="prix">10 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/camera.jpg" alt="Caméra">
                <h3>Caméra Pro</h3>
                <p>Qualité professionnelle</p>
                <p class="prix">65 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/printer.jpg" alt="Imprimante">
                <h3>Imprimante Jet</h3>
                <p>Rapide et fiable</p>
                <p class="prix">200 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/router.jpg" alt="Routeur">
                <h3>Routeur WiFi 6</h3>
                <p>Connexion rapide</p>
                <p class="prix">35 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/speaker.jpg" alt="Enceinte">
                <h3>Enceinte Bluetooth</h3>
                <p>Son puissant</p>
                <p class="prix">10 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/mouse.jpg" alt="Souris">
                <h3>Souris Gamer</h3>
                <p>Précision extrême</p>
                <p class="prix">3 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/keyboard.jpg" alt="Clavier">
                <h3>Clavier Mécanique</h3>
                <p>Confort et rapidité</p>
                <p class="prix">5 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/projector.jpg" alt="Projecteur">
                <h3>Projecteur HD</h3>
                <p>Cinéma maison</p>
                <p class="prix">45 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/drone.jpg" alt="Drone">
                <h3>Drone Cam</h3>
                <p>Vue aérienne</p>
                <p class="prix">20 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/gaming_console.jpg" alt="Console">
                <h3>Console NextGen</h3>
                <p>Jeux immersifs</p>
                <p class="prix">70 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/microphone.jpg" alt="Micro">
                <h3>Micro Studio</h3>
                <p>Qualité audio</p>
                <p class="prix">80 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/webcam.jpg" alt="Webcam">
                <h3>Webcam HD</h3>
                <p>Visio claire</p>
                <p class="prix">20 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/powerbank.jpg" alt="Powerbank">
                <h3>Powerbank 20000mAh</h3>
                <p>Énergie portable</p>
                <p class="prix">15 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/vr.jpg" alt="VR">
                <h3>Casque VR</h3>
                <p>Réalité virtuelle</p>
                <p class="prix">15 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

            <div class="card"><img src="images/home_assistant.jpg" alt="Assistant">
                <h3>Assistant Vocal</h3>
                <p>Maison connectée</p>
                <p class="prix">75 000 FCFA</p><a href="ajout_panier.php" class="btn">Add</a>
            </div>

        </div>
    </main>
    <footer>
        <p>&copy; Copyright <?= date("Y") ?> AMIRAL Electro. Coorp. - Vente en ligne de produits électroniques</p>
    </footer>
</body>

</html>