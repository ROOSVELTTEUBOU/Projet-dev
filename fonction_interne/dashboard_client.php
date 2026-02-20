<?php
session_start();

if (!isset($_SESSION['id_client'])) {
    header('Location: ../fonction/login_client.php');
    exit();
}

function fetchScalar(mysqli $conn, string $sql, string $types = '', array $params = [], $default = 0)
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return $default;
    }

    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        return $default;
    }

    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return $default;
    }

    $row = $result->fetch_row();
    $stmt->close();

    return $row[0] ?? $default;
}

$clientId = (int) $_SESSION['id_client'];
$prenom = isset($_SESSION['prenom']) ? trim((string) $_SESSION['prenom']) : '';
$nom = isset($_SESSION['nom']) ? trim((string) $_SESSION['nom']) : '';
$clientName = trim($prenom . ' ' . $nom);
if ($clientName === '') {
    $clientName = 'Client';
}

if (!isset($_SESSION['panier']) || !is_array($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$productCatalog = [
    [
        'id' => 'smartphone_x200',
        'name' => 'Smartphone X200',
        'description' => 'Performance et elegance pour un usage quotidien intensif.',
        'price' => 90000,
        'image' => '../images/smartphone.jpg',
    ],
    [
        'id' => 'ordinateur_probook',
        'name' => 'Ordinateur ProBook',
        'description' => 'Puissance et stabilite pour vos projets professionnels.',
        'price' => 300000,
        'image' => '../images/laptop.jpg',
    ],
    [
        'id' => 'casque_audio_hd',
        'name' => 'Casque Audio HD',
        'description' => 'Son immersif avec reduction de bruit et confort longue duree.',
        'price' => 5000,
        'image' => '../images/headphones.jpg',
    ],
    [
        'id' => 'tablette_tabmax',
        'name' => 'Tablette TabMax',
        'description' => 'Mobilite et confort pour lecture, video et bureautique mobile.',
        'price' => 50000,
        'image' => '../images/tablette.jpg',
    ],
    [
        'id' => 'television_4k',
        'name' => 'Television 4K',
        'description' => 'Image ultra HD et couleurs precises pour salon multimedia.',
        'price' => 85000,
        'image' => '../images/tv.jpg',
    ],
    [
        'id' => 'smartwatch_fit',
        'name' => 'Smartwatch Fit',
        'description' => 'Suivi sante, notifications et autonomie adaptee au quotidien.',
        'price' => 10000,
        'image' => '../images/smartwatch.jpg',
    ],
    [
        'id' => 'camera_pro',
        'name' => 'Camera Pro',
        'description' => 'Qualite professionnelle pour photo et video haute definition.',
        'price' => 65000,
        'image' => '../images/camera.jpg',
    ],
    [
        'id' => 'imprimante_jet',
        'name' => 'Imprimante Jet',
        'description' => 'Impression rapide et fiable pour maison et bureau.',
        'price' => 200000,
        'image' => '../images/printer.jpg',
    ],
    [
        'id' => 'routeur_wifi_6',
        'name' => 'Routeur WiFi 6',
        'description' => 'Connexion rapide et stable pour plusieurs appareils connectes.',
        'price' => 35000,
        'image' => '../images/router.jpg',
    ],
    [
        'id' => 'enceinte_bluetooth',
        'name' => 'Enceinte Bluetooth',
        'description' => 'Son puissant et portable pour musique en interieur et exterieur.',
        'price' => 10000,
        'image' => '../images/speaker.jpg',
    ],
    [
        'id' => 'souris_gamer',
        'name' => 'Souris Gamer',
        'description' => 'Precision extreme et reactivite optimale pour jeu et design.',
        'price' => 3000,
        'image' => '../images/mouse.jpg',
    ],
    [
        'id' => 'clavier_mecanique',
        'name' => 'Clavier Mecanique',
        'description' => 'Confort de frappe et rapidite pour travail et gaming.',
        'price' => 5000,
        'image' => '../images/keyboard.jpg',
    ],
    [
        'id' => 'projecteur_hd',
        'name' => 'Projecteur HD',
        'description' => 'Cinema maison avec projection nette sur grand ecran.',
        'price' => 50000,
        'image' => '../images/projector.jpg',
    ],
    [
        'id' => 'drone_cam',
        'name' => 'Drone Cam',
        'description' => 'Vue aerienne stable pour capture photo et video creative.',
        'price' => 50000,
        'image' => '../images/drone.jpg',
    ],
    [
        'id' => 'console_nextgen',
        'name' => 'Console NextGen',
        'description' => 'Jeux immersifs et performances nouvelle generation.',
        'price' => 70000,
        'image' => '../images/gaming_console.jpg',
    ],
    [
        'id' => 'micro_studio',
        'name' => 'Micro Studio',
        'description' => 'Qualite audio claire pour streaming, voix et enregistrement.',
        'price' => 10000,
        'image' => '../images/microphone.jpg',
    ],
    [
        'id' => 'webcam_hd',
        'name' => 'Webcam HD',
        'description' => 'Visio claire pour reunion, cours et creation de contenu.',
        'price' => 20000,
        'image' => '../images/webcam.jpg',
    ],
    [
        'id' => 'powerbank_20000mah',
        'name' => 'Powerbank 20000mAh',
        'description' => 'Energie portable pour recharger vos appareils en deplacement.',
        'price' => 15000,
        'image' => '../images/powerbank.jpg',
    ],
    [
        'id' => 'casque_vr',
        'name' => 'Casque VR',
        'description' => 'Realite virtuelle immersive pour jeu et experiences interactives.',
        'price' => 15000,
        'image' => '../images/vr.jpg',
    ],
    [
        'id' => 'assistant_vocal',
        'name' => 'Assistant Vocal',
        'description' => 'Maison connectee avec controle vocal des appareils.',
        'price' => 85000,
        'image' => '../images/home_assistant.jpg',
    ],
];

$catalogById = [];
foreach ($productCatalog as $catalogProduct) {
    $catalogById[$catalogProduct['id']] = $catalogProduct;
}

function dashboard_client_redirect(string $anchor = 'panier-express'): void
{
    $safeAnchor = preg_replace('/[^A-Za-z0-9_-]/', '', $anchor);
    $baseUrl = strtok((string) ($_SERVER['REQUEST_URI'] ?? 'dashboard_client.php'), '?');
    if ($baseUrl === false || $baseUrl === '') {
        $baseUrl = 'dashboard_client.php';
    }
    if ($safeAnchor !== '') {
        $baseUrl .= '#' . $safeAnchor;
    }

    header('Location: ' . $baseUrl);
    exit();
}

$pendingPlaceOrder = false;
$postReturnAnchor = 'panier-express';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_action'])) {
    $cartAction = trim((string) ($_POST['cart_action'] ?? ''));
    $productId = trim((string) ($_POST['product_id'] ?? ''));
    $cartMessage = '';
    $postReturnAnchor = trim((string) ($_POST['return_anchor'] ?? 'panier-express'));
    if ($postReturnAnchor === '') {
        $postReturnAnchor = 'panier-express';
    }
    $postReturnAnchor = preg_replace('/[^A-Za-z0-9_-]/', '', $postReturnAnchor);
    if ($postReturnAnchor === '') {
        $postReturnAnchor = 'panier-express';
    }

    if ($cartAction === 'place_order') {
        $pendingPlaceOrder = true;
    } else {
        $selectedProduct = $catalogById[$productId] ?? null;

        if ($cartAction === 'add' || $cartAction === 'inc') {
            if ($selectedProduct === null) {
                if ($cartAction === 'inc' && isset($_SESSION['panier'][$productId])) {
                    $currentEntry = $_SESSION['panier'][$productId];
                    $displayName = 'Produit';
                    if (is_array($currentEntry) && isset($currentEntry['libelle']) && trim((string) $currentEntry['libelle']) !== '') {
                        $displayName = (string) $currentEntry['libelle'];
                        $_SESSION['panier'][$productId]['quantite'] = (int) ($_SESSION['panier'][$productId]['quantite'] ?? 0) + 1;
                    } elseif (is_numeric($currentEntry)) {
                        $_SESSION['panier'][$productId] = (int) $currentEntry + 1;
                    }
                    $cartMessage = 'Quantite mise a jour pour ' . $displayName . '.';
                } else {
                    $cartMessage = 'Produit introuvable dans le catalogue.';
                }
            } else {
                if (!isset($_SESSION['panier'][$productId]) || !is_array($_SESSION['panier'][$productId])) {
                    $_SESSION['panier'][$productId] = [
                        'libelle' => $selectedProduct['name'],
                        'description' => $selectedProduct['description'],
                        'prix' => (float) $selectedProduct['price'],
                        'image' => $selectedProduct['image'],
                        'quantite' => 0,
                    ];
                }

                $currentQty = (int) ($_SESSION['panier'][$productId]['quantite'] ?? 0);
                $_SESSION['panier'][$productId]['quantite'] = $currentQty + 1;
                $_SESSION['panier'][$productId]['prix'] = (float) $selectedProduct['price'];
                $_SESSION['panier'][$productId]['libelle'] = $selectedProduct['name'];
                $_SESSION['panier'][$productId]['description'] = $selectedProduct['description'];
                $_SESSION['panier'][$productId]['image'] = $selectedProduct['image'];
                $cartMessage = $selectedProduct['name'] . ' ajoute au panier.';
            }
        } elseif ($cartAction === 'dec') {
            if (!isset($_SESSION['panier'][$productId])) {
                $cartMessage = 'Produit absent du panier.';
            } else {
                $currentEntry = $_SESSION['panier'][$productId];
                $displayName = $selectedProduct['name'] ?? 'Produit';
                if (is_array($currentEntry) && isset($currentEntry['libelle']) && trim((string) $currentEntry['libelle']) !== '') {
                    $displayName = (string) $currentEntry['libelle'];
                }

                $currentQty = 1;
                if (is_array($currentEntry)) {
                    $currentQty = (int) ($currentEntry['quantite'] ?? 1);
                } elseif (is_numeric($currentEntry)) {
                    $currentQty = (int) $currentEntry;
                }

                if ($currentQty <= 1) {
                    unset($_SESSION['panier'][$productId]);
                    $cartMessage = $displayName . ' retire du panier.';
                } else {
                    if (is_array($currentEntry)) {
                        $_SESSION['panier'][$productId]['quantite'] = $currentQty - 1;
                    } else {
                        $_SESSION['panier'][$productId] = $currentQty - 1;
                    }
                    $cartMessage = 'Quantite mise a jour pour ' . $displayName . '.';
                }
            }
        } elseif ($cartAction === 'remove') {
            if (!isset($_SESSION['panier'][$productId])) {
                $cartMessage = 'Produit absent du panier.';
            } else {
                $currentEntry = $_SESSION['panier'][$productId];
                $displayName = $selectedProduct['name'] ?? 'Produit';
                if (is_array($currentEntry) && isset($currentEntry['libelle']) && trim((string) $currentEntry['libelle']) !== '') {
                    $displayName = (string) $currentEntry['libelle'];
                }

                unset($_SESSION['panier'][$productId]);
                $cartMessage = $displayName . ' retire du panier.';
            }
        } elseif ($cartAction === 'clear') {
            $_SESSION['panier'] = [];
            $cartMessage = 'Le panier a ete vide.';
        } else {
            $cartMessage = 'Action panier invalide.';
        }

        $_SESSION['dashboard_cart_notice'] = $cartMessage;
        dashboard_client_redirect($postReturnAnchor);
    }
}

$cartNotice = '';
if (isset($_SESSION['dashboard_cart_notice'])) {
    $cartNotice = (string) $_SESSION['dashboard_cart_notice'];
    unset($_SESSION['dashboard_cart_notice']);
}

$cartRows = [];
$cartTotalItems = 0;
$cartTotalAmount = 0.0;

foreach ($_SESSION['panier'] as $key => $rawItem) {
    $itemKey = (string) $key;
    $catalogItem = $catalogById[$itemKey] ?? null;

    $name = $catalogItem['name'] ?? ('Produit #' . $itemKey);
    $description = $catalogItem['description'] ?? 'Produit ajoute dans votre panier.';
    $image = $catalogItem['image'] ?? '../images/smartphone.jpg';
    $qty = 1;
    $price = isset($catalogItem['price']) ? (float) $catalogItem['price'] : 0.0;

    if (is_array($rawItem)) {
        if (isset($rawItem['libelle'])) {
            $name = (string) $rawItem['libelle'];
        } elseif (isset($rawItem['nom'])) {
            $name = (string) $rawItem['nom'];
        } elseif (isset($rawItem['name'])) {
            $name = (string) $rawItem['name'];
        }

        if (isset($rawItem['description']) && trim((string) $rawItem['description']) !== '') {
            $description = (string) $rawItem['description'];
        }

        if (isset($rawItem['image']) && trim((string) $rawItem['image']) !== '') {
            $image = (string) $rawItem['image'];
        }

        if (isset($rawItem['quantite'])) {
            $qty = (int) $rawItem['quantite'];
        } elseif (isset($rawItem['quantity'])) {
            $qty = (int) $rawItem['quantity'];
        } elseif (isset($rawItem['qty'])) {
            $qty = (int) $rawItem['qty'];
        }

        if (isset($rawItem['prix'])) {
            $price = (float) $rawItem['prix'];
        } elseif (isset($rawItem['price'])) {
            $price = (float) $rawItem['price'];
        } elseif (isset($rawItem['montant'])) {
            $price = (float) $rawItem['montant'];
        }
    } elseif (is_numeric($rawItem)) {
        $qty = (int) $rawItem;
    }

    if ($qty < 1) {
        continue;
    }

    $lineTotal = $qty * $price;

    $cartRows[] = [
        'key' => $itemKey,
        'name' => $name,
        'description' => $description,
        'image' => $image,
        'qty' => $qty,
        'price' => $price,
        'line_total' => $lineTotal,
    ];

    $cartTotalItems += $qty;
    $cartTotalAmount += $lineTotal;
}

$stats = [
    'orders_total' => 0,
    'orders_pending' => 0,
    'orders_delivered' => 0,
    'amount_paid' => 0.0,
    'last_order_date' => null,
];

$clientProfile = [
    'email' => '-',
    'contact' => '-',
    'ville' => '-',
];

$recentOrders = [];
$recentPayments = [];
$dbError = null;

$dbPath = __DIR__ . '/../connexion_bd/connexion_bd.php';
if (file_exists($dbPath)) {
    require $dbPath;

    if (isset($conn) && $conn instanceof mysqli) {
        if ($pendingPlaceOrder) {
            if (empty($_SESSION['panier']) || !is_array($_SESSION['panier'])) {
                $_SESSION['dashboard_cart_notice'] = 'Votre panier est vide. Ajoutez des produits avant de passer commande.';
                $conn->close();
                dashboard_client_redirect($postReturnAnchor);
            }

            $orderError = null;
            $createdOrderId = 0;
            $createdPaymentId = 0;
            $savedArticles = 0;
            $orderAmount = max(0.0, (float) $cartTotalAmount);

            if ($orderAmount <= 0) {
                $orderError = 'Montant de commande invalide.';
            }

            if ($orderError === null && !$conn->begin_transaction()) {
                $orderError = 'Transaction indisponible.';
            } elseif ($orderError === null) {
                $orderDate = date('Y-m-d');
                $orderStatus = 'En attente';
                $insertOrderStmt = $conn->prepare('INSERT INTO Commande_Client (date_commande, id_client, statut) VALUES (?, ?, ?)');

                if (!$insertOrderStmt) {
                    $orderError = 'Creation de commande indisponible.';
                } else {
                    $insertOrderStmt->bind_param('sis', $orderDate, $clientId, $orderStatus);
                    if ($insertOrderStmt->execute()) {
                        $createdOrderId = (int) $insertOrderStmt->insert_id;
                    } else {
                        $orderError = 'Echec de creation de la commande.';
                    }
                    $insertOrderStmt->close();
                }

                if ($orderError === null && $createdOrderId > 0) {
                    $productMap = [];
                    $productsResult = $conn->query('SELECT id_produit, libelle FROM Produit');
                    if ($productsResult) {
                        while ($productRow = $productsResult->fetch_assoc()) {
                            $labelKey = strtolower(trim((string) ($productRow['libelle'] ?? '')));
                            if ($labelKey !== '') {
                                $productMap[$labelKey] = (int) $productRow['id_produit'];
                            }
                        }
                        $productsResult->free();
                    }

                    $insertLineStmt = $conn->prepare('INSERT INTO Concerner_Client (id_commande, id_produit, quantite) VALUES (?, ?, ?)');
                    if ($insertLineStmt) {
                        foreach ($_SESSION['panier'] as $itemKey => $rawItem) {
                            $qty = 0;
                            $label = '';

                            if (is_array($rawItem)) {
                                $qty = (int) ($rawItem['quantite'] ?? $rawItem['quantity'] ?? $rawItem['qty'] ?? 0);
                                $label = trim((string) ($rawItem['libelle'] ?? $rawItem['nom'] ?? $rawItem['name'] ?? ''));
                            } elseif (is_numeric($rawItem)) {
                                $qty = (int) $rawItem;
                            }

                            if ($qty < 1) {
                                continue;
                            }

                            $mappedProductId = 0;
                            if (ctype_digit((string) $itemKey)) {
                                $mappedProductId = (int) $itemKey;
                            }

                            if ($mappedProductId <= 0 && $label !== '') {
                                $mappedProductId = (int) ($productMap[strtolower($label)] ?? 0);
                            }

                            if ($mappedProductId <= 0) {
                                continue;
                            }

                            $insertLineStmt->bind_param('iii', $createdOrderId, $mappedProductId, $qty);
                            if ($insertLineStmt->execute()) {
                                $savedArticles += $qty;
                            }
                        }
                        $insertLineStmt->close();
                    }

                    $paymentType = 'Client';
                    $paymentMode = 'MobileMoney';
                    $paymentDate = date('Y-m-d');
                    $paymentStatus = 'En attente';

                    $insertPaymentStmt = $conn->prepare('INSERT INTO Paiement (type, id_commande, montant, mode, date_paiement, statut) VALUES (?, ?, ?, ?, ?, ?)');
                    if (!$insertPaymentStmt) {
                        $orderError = 'Creation du paiement indisponible.';
                    } else {
                        $insertPaymentStmt->bind_param('sidsss', $paymentType, $createdOrderId, $orderAmount, $paymentMode, $paymentDate, $paymentStatus);
                        if ($insertPaymentStmt->execute()) {
                            $createdPaymentId = (int) $insertPaymentStmt->insert_id;
                        } else {
                            $orderError = 'Echec de creation du paiement.';
                        }
                        $insertPaymentStmt->close();
                    }
                }

                if ($orderError === null && $createdOrderId > 0 && $createdPaymentId > 0) {
                    $conn->commit();
                } else {
                    $conn->rollback();
                }
            }

            if ($orderError === null && $createdOrderId > 0 && $createdPaymentId > 0) {
                $_SESSION['panier'] = [];
                $_SESSION['dashboard_cart_notice'] = 'Commande #' . $createdOrderId . ' creee. Finalisez le paiement Mobile Money / Orange Money.';
                $conn->close();
                header('Location: ../paiement.php?order_id=' . $createdOrderId . '&payment_id=' . $createdPaymentId);
                exit();
            }

            $_SESSION['dashboard_cart_notice'] = 'Impossible de passer la commande: ' . ($orderError ?? 'Erreur inconnue.');
            $conn->close();
            dashboard_client_redirect($postReturnAnchor);
        }

        $stats['orders_total'] = (int) fetchScalar(
            $conn,
            'SELECT COUNT(*) FROM Commande_Client WHERE id_client = ?',
            'i',
            [$clientId],
            0
        );

        $stats['orders_pending'] = (int) fetchScalar(
            $conn,
            "SELECT COUNT(*) FROM Commande_Client WHERE id_client = ? AND statut = 'En attente'",
            'i',
            [$clientId],
            0
        );

        $stats['orders_delivered'] = (int) fetchScalar(
            $conn,
            "SELECT COUNT(*) FROM Commande_Client WHERE id_client = ? AND statut LIKE 'Liv%'",
            'i',
            [$clientId],
            0
        );

        $stats['amount_paid'] = (float) fetchScalar(
            $conn,
            "SELECT COALESCE(SUM(p.montant), 0) FROM Paiement p INNER JOIN Commande_Client c ON c.id_commande = p.id_commande WHERE c.id_client = ? AND p.type = 'Client'",
            'i',
            [$clientId],
            0
        );

        $stats['last_order_date'] = fetchScalar(
            $conn,
            'SELECT MAX(date_commande) FROM Commande_Client WHERE id_client = ?',
            'i',
            [$clientId],
            null
        );

        $profileStmt = $conn->prepare('SELECT email, contact, ville FROM Client WHERE id_client = ? LIMIT 1');
        if ($profileStmt) {
            $profileStmt->bind_param('i', $clientId);
            if ($profileStmt->execute()) {
                $profileResult = $profileStmt->get_result();
                if ($profileResult && $profileRow = $profileResult->fetch_assoc()) {
                    $clientProfile['email'] = $profileRow['email'] ?: '-';
                    $clientProfile['contact'] = $profileRow['contact'] ?: '-';
                    $clientProfile['ville'] = $profileRow['ville'] ?: '-';
                }
            }
            $profileStmt->close();
        }

        $ordersStmt = $conn->prepare('SELECT id_commande, date_commande, statut FROM Commande_Client WHERE id_client = ? ORDER BY date_commande DESC, id_commande DESC LIMIT 5');
        if ($ordersStmt) {
            $ordersStmt->bind_param('i', $clientId);
            if ($ordersStmt->execute()) {
                $ordersResult = $ordersStmt->get_result();
                while ($ordersResult && $row = $ordersResult->fetch_assoc()) {
                    $recentOrders[] = $row;
                }
            }
            $ordersStmt->close();
        }

        $paymentsStmt = $conn->prepare("SELECT p.date_paiement, p.montant, p.mode, p.statut FROM Paiement p INNER JOIN Commande_Client c ON c.id_commande = p.id_commande WHERE c.id_client = ? AND p.type = 'Client' ORDER BY p.date_paiement DESC, p.id_paiement DESC LIMIT 5");
        if ($paymentsStmt) {
            $paymentsStmt->bind_param('i', $clientId);
            if ($paymentsStmt->execute()) {
                $paymentsResult = $paymentsStmt->get_result();
                while ($paymentsResult && $row = $paymentsResult->fetch_assoc()) {
                    $recentPayments[] = $row;
                }
            }
            $paymentsStmt->close();
        }

        $conn->close();
    } else {
        $dbError = 'Connexion base indisponible.';
    }
} else {
    $dbError = 'Fichier de connexion base introuvable.';
}

if ($pendingPlaceOrder) {
    $_SESSION['dashboard_cart_notice'] = 'Impossible de passer la commande: base de donnees indisponible.';
    dashboard_client_redirect($postReturnAnchor);
}

$actions = [
    [
        'title' => 'Catalogue',
        'description' => 'Consulter tous les produits disponibles.',
        'href' => '../client/catalogue.php',
    ],
    [
        'title' => 'Mon panier',
        'description' => 'Verifier, modifier ou vider votre panier.',
        'href' => '../client/panier.php',
    ],
    [
        'title' => 'Mes commandes',
        'description' => 'Suivre le statut de vos commandes clients.',
        'href' => '../client/commande_client.php',
    ],
    [
        'title' => 'Mes paiements',
        'description' => 'Voir les paiements et leur statut.',
        'href' => '../client/paiement_client.php',
    ],
    [
        'title' => 'Mes factures',
        'description' => 'Consulter et telecharger les factures.',
        'href' => '../client/facture_client.php',
    ],
    [
        'title' => 'Mon profil',
        'description' => 'Mettre a jour vos informations personnelles.',
        'href' => '../client/profil_client.php',
    ],
    [
        'title' => 'Support',
        'description' => 'Contacter le service client.',
        'href' => '../client/support_client.php',
    ],
];

$availableCount = 0;
foreach ($actions as $action) {
    if (file_exists(__DIR__ . '/' . $action['href'])) {
        $availableCount++;
    }
}

$featureItems = [
    'Consulter le catalogue electronique',
    'Ajouter et gerer les articles dans le panier',
    'Passer une commande client',
    'Suivre les statuts de commande',
    'Consulter et suivre les paiements',
    'Acceder aux factures',
    'Mettre a jour les informations du compte',
    'Se deconnecter de facon securisee',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Client</title>
  <style>
    :root {
      --white: #ffffff;
      --blue-50: #eaf2ff;
      --blue-100: #d7e6ff;
      --blue-300: #82b2ff;
      --blue-500: #2f8dff;
      --blue-600: #0066ff;
      --blue-700: #004fd4;
      --blue-800: #082f8f;
      --black: #070d18;
      --slate: #415378;
      --border: #cbddff;
      --shadow: 0 12px 34px rgba(7, 13, 24, 0.1);
      --blue-glow: 0 12px 30px rgba(0, 102, 255, 0.24);
      --radius: 14px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
      color: var(--black);
      background:
        radial-gradient(circle at 12% -10%, rgba(0, 102, 255, 0.26), transparent 36%),
        radial-gradient(circle at 88% 0%, rgba(47, 141, 255, 0.24), transparent 32%),
        linear-gradient(170deg, var(--blue-50), #f5f9ff 48%, var(--white));
    }

    .topbar {
      background: linear-gradient(95deg, #040a16 0%, #0a2f8f 44%, var(--blue-600) 100%);
      color: var(--white);
      padding: 18px 26px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      flex-wrap: wrap;
      box-shadow: 0 16px 36px rgba(0, 70, 180, 0.3);
    }

    .brand h1 {
      margin: 0;
      font-size: 22px;
      letter-spacing: 0.3px;
      text-shadow: 0 0 14px rgba(130, 178, 255, 0.45);
    }

    .brand p {
      margin: 4px 0 0;
      color: #d8e8ff;
      font-size: 13px;
    }

    .client-box {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: linear-gradient(160deg, #ffffff, #e8f1ff);
      color: var(--blue-700);
      border: 2px solid rgba(0, 102, 255, 0.35);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      box-shadow: 0 0 0 4px rgba(0, 102, 255, 0.14);
    }

    .logout-link {
      text-decoration: none;
      color: var(--white);
      border: 1px solid rgba(194, 220, 255, 0.9);
      border-radius: 10px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 600;
      transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .logout-link:hover {
      background: #0a70ff;
      border-color: #0a70ff;
      transform: translateY(-1px);
      box-shadow: var(--blue-glow);
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    .cart-link {
      text-decoration: none;
      color: var(--blue-800);
      background: #ffffff;
      border: 1px solid #c7dcff;
      border-radius: 10px;
      padding: 9px 12px;
      font-size: 12px;
      font-weight: 700;
      transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .cart-link:hover {
      transform: translateY(-1px);
      border-color: #7fb1ff;
      box-shadow: 0 10px 22px rgba(0, 79, 212, 0.2);
    }

    .container {
      max-width: 1180px;
      margin: 24px auto 40px;
      padding: 0 18px;
      display: grid;
      gap: 18px;
    }

    .panel {
      background: linear-gradient(180deg, #ffffff, #f4f9ff);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 18px;
    }

    .intro {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 18px;
    }

    .intro h2 {
      margin: 0 0 8px;
      font-size: 22px;
    }

    .intro p {
      margin: 0;
      color: var(--slate);
      line-height: 1.5;
    }

    .mini-list {
      margin: 12px 0 0;
      padding-left: 18px;
      color: var(--slate);
    }

    .mini-list li {
      margin: 6px 0;
    }

    .status-box {
      background: linear-gradient(160deg, #f0f6ff 0%, #dce9ff 100%);
      border: 1px solid #bad2ff;
      border-radius: 12px;
      padding: 14px;
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.7), 0 12px 26px rgba(10, 47, 143, 0.14);
    }

    .status-box .label {
      color: var(--slate);
      font-size: 13px;
      margin-bottom: 6px;
    }

    .status-box .value {
      font-size: 28px;
      font-weight: 700;
      color: var(--blue-700);
      text-shadow: 0 0 12px rgba(47, 141, 255, 0.3);
    }

    .stats {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 14px;
    }

    .stat {
      background: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%);
      border: 1px solid #c8dbff;
      border-radius: 12px;
      padding: 14px;
      transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }

    .stat:hover {
      transform: translateY(-2px);
      border-color: #8db9ff;
      box-shadow: 0 10px 24px rgba(0, 79, 212, 0.2);
    }

    .stat .name {
      color: var(--slate);
      font-size: 13px;
    }

    .stat .num {
      margin-top: 8px;
      font-size: 26px;
      font-weight: 700;
      color: var(--blue-700);
      letter-spacing: 0.2px;
    }

    .stat .sub {
      margin-top: 6px;
      color: var(--slate);
      font-size: 12px;
    }

    .actions-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 14px;
    }

    .action {
      border: 1px solid #c7dbff;
      border-radius: 12px;
      padding: 14px;
      background: linear-gradient(180deg, #ffffff, #f2f7ff);
      display: block;
      text-decoration: none;
      color: inherit;
      transition: transform 0.16s ease, box-shadow 0.16s ease;
      position: relative;
      overflow: hidden;
    }

    .action::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #66a6ff, var(--blue-600));
      opacity: 0.95;
    }

    .action:hover {
      transform: translateY(-3px);
      box-shadow: 0 14px 28px rgba(0, 79, 212, 0.2);
    }

    .action.disabled {
      opacity: 0.72;
      background: linear-gradient(180deg, #f7f9fd, #eff3fb);
      cursor: not-allowed;
      pointer-events: none;
    }

    .tag {
      display: inline-block;
      margin-bottom: 8px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      padding: 4px 9px;
      letter-spacing: 0.2px;
    }

    .tag.live {
      background: #d8e9ff;
      color: #0a44b5;
    }

    .tag.todo {
      background: #ebf0fa;
      color: #4a5b79;
    }

    .action h3 {
      margin: 0;
      font-size: 17px;
      color: var(--black);
    }

    .action p {
      margin: 7px 0 0;
      font-size: 13px;
      line-height: 1.45;
      color: var(--slate);
    }

    .tables {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .table-box h3 {
      margin: 0 0 12px;
      font-size: 17px;
      color: var(--blue-800);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
      background: #ffffff;
      border-radius: 10px;
      overflow: hidden;
    }

    th,
    td {
      border-bottom: 1px solid #e7eefb;
      padding: 9px 6px;
      text-align: left;
    }

    th {
      color: var(--slate);
      font-weight: 600;
      background: #edf4ff;
    }

    tbody tr:hover {
      background: #f3f8ff;
    }

    .empty {
      color: var(--slate);
      font-size: 13px;
      margin: 8px 0 0;
    }

    .error {
      border: 1px solid #ffd6d6;
      background: #fff5f5;
      color: #8a1f1f;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: 13px;
    }

    .notice {
      border: 1px solid #bee6cb;
      background: #effcf3;
      color: #1d6d3a;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: 13px;
    }

    .status-cart {
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px dashed rgba(52, 80, 125, 0.35);
      color: #2a4a76;
      font-size: 12px;
      line-height: 1.45;
    }

    .status-cart a {
      color: #0d4fcf;
      text-decoration: none;
      font-weight: 700;
    }

    .quick-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 12px;
    }

    .quick-actions form {
      margin: 0;
    }

    .quick-btn {
      border: none;
      border-radius: 999px;
      padding: 8px 12px;
      font-size: 12px;
      font-weight: 700;
      color: #ffffff;
      background: linear-gradient(110deg, #0076ff, #0350d8);
      cursor: pointer;
      transition: transform 0.16s ease, box-shadow 0.16s ease;
      box-shadow: 0 8px 18px rgba(3, 80, 216, 0.25);
    }

    .quick-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 24px rgba(3, 80, 216, 0.3);
    }

    .shop-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 14px;
    }

    .products-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
      margin-top: 12px;
    }

    .product-card {
      border: 1px solid #c7dbff;
      border-radius: 12px;
      background: linear-gradient(180deg, #ffffff, #f2f7ff);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 10px 24px rgba(7, 13, 24, 0.08);
      transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 16px 28px rgba(0, 79, 212, 0.18);
    }

    .product-image {
      width: 100%;
      height: 180px;
      object-fit: cover;
      display: block;
      background: #e9f2ff;
    }

    .product-body {
      padding: 12px;
      display: grid;
      gap: 8px;
    }

    .product-card h3 {
      margin: 0;
      font-size: 16px;
      color: #0d2f73;
    }

    .product-desc {
      margin: 0;
      font-size: 13px;
      color: var(--slate);
      line-height: 1.45;
      min-height: 54px;
    }

    .product-price {
      font-size: 18px;
      font-weight: 800;
      color: var(--blue-700);
      letter-spacing: 0.2px;
    }

    .btn-add {
      width: 100%;
      border: none;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: 13px;
      font-weight: 700;
      color: #ffffff;
      background: linear-gradient(110deg, #0080ff, #0054e6);
      cursor: pointer;
      transition: transform 0.16s ease, box-shadow 0.16s ease;
      box-shadow: 0 10px 20px rgba(0, 84, 230, 0.24);
    }

    .btn-add:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 26px rgba(0, 84, 230, 0.32);
    }

    .cart-panel h3 {
      margin: 0;
      color: var(--blue-800);
    }

    .cart-summary {
      margin-top: 10px;
      border: 1px solid #c7dbff;
      border-radius: 10px;
      background: #f0f6ff;
      padding: 10px;
      display: grid;
      gap: 8px;
    }

    .cart-summary-line {
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #264170;
      font-size: 13px;
    }

    .cart-items {
      display: grid;
      gap: 10px;
      margin-top: 12px;
    }

    .cart-item {
      border: 1px solid #d4e4ff;
      border-radius: 10px;
      background: #ffffff;
      padding: 8px;
      display: grid;
      grid-template-columns: 70px 1fr;
      gap: 8px;
      align-items: center;
    }

    .cart-item-image {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 8px;
      background: #e9f2ff;
    }

    .cart-item-name {
      font-size: 14px;
      font-weight: 700;
      margin: 0;
      color: #0d2f73;
    }

    .cart-item-meta {
      margin-top: 3px;
      color: var(--slate);
      font-size: 12px;
    }

    .cart-item-total {
      margin-top: 5px;
      font-size: 13px;
      font-weight: 700;
      color: var(--blue-700);
    }

    .cart-controls {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 8px;
    }

    .cart-controls form {
      margin: 0;
    }

    .btn-small {
      border: 1px solid #a8c8ff;
      border-radius: 8px;
      padding: 4px 8px;
      font-size: 12px;
      font-weight: 700;
      color: #0f49b2;
      background: #edf4ff;
      cursor: pointer;
    }

    .btn-small.danger {
      border-color: #ffc5c5;
      color: #9f2525;
      background: #fff2f2;
    }

    .btn-clear {
      margin-top: 12px;
      width: 100%;
      border: 1px solid #ffc7c7;
      border-radius: 10px;
      padding: 10px;
      font-size: 13px;
      font-weight: 700;
      color: #a12020;
      background: #fff3f3;
      cursor: pointer;
    }

    .btn-order {
      margin-top: 8px;
      width: 100%;
      border: none;
      border-radius: 10px;
      padding: 11px;
      font-size: 13px;
      font-weight: 700;
      color: #ffffff;
      background: linear-gradient(110deg, #009e53, #047a40);
      cursor: pointer;
      box-shadow: 0 10px 20px rgba(4, 122, 64, 0.25);
      transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .btn-order:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 24px rgba(4, 122, 64, 0.3);
    }

    .cart-links {
      margin-top: 12px;
      display: grid;
      gap: 8px;
    }

    .cart-links a {
      text-decoration: none;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: 13px;
      font-weight: 700;
      text-align: center;
      border: 1px solid #c5dcff;
      color: #0f49b2;
      background: #eef5ff;
    }

    @media (max-width: 1050px) {
      .stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .actions-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .intro,
      .tables,
      .shop-grid {
        grid-template-columns: 1fr;
      }

      .products-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 620px) {
      .stats,
      .actions-grid {
        grid-template-columns: 1fr;
      }

      .products-grid {
        grid-template-columns: 1fr;
      }

      .topbar {
        padding: 14px;
      }

      .brand h1 {
        font-size: 19px;
      }
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <h1>Espace Client</h1>
      <p>Tableau de bord de gestion de compte</p>
    </div>

    <div class="client-box">
      <div style="text-align:right;">
        <div style="font-weight:700;"><?php echo htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="font-size:12px;color:#d5e6ff;">ID client: <?php echo $clientId; ?></div>
      </div>
      <div class="avatar"><?php echo strtoupper(substr($clientName, 0, 1)); ?></div>
      <div class="header-actions">
        <a class="logout-link" href="logout.php">Se deconnecter</a>
      </div>
    </div>
  </header>

  <main class="container">
    <?php if ($dbError !== null): ?>
      <div class="error"><?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($cartNotice !== ''): ?>
      <div class="notice"><?php echo htmlspecialchars($cartNotice, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <section class="panel intro">
      <div>
        <h2>Bienvenue sur votre dashboard</h2>
        <p>Ce panneau regroupe les principales actions client: consultation du catalogue electronique, gestion du panier, commandes, paiements, factures et suivi de compte.</p>
        <ul class="mini-list">
          <?php foreach ($featureItems as $item): ?>
            <li><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        </ul>
        <div class="quick-actions">
          <?php foreach (array_slice($productCatalog, 0, 4) as $quickProduct): ?>
            <form method="post">
              <input type="hidden" name="cart_action" value="add">
              <input type="hidden" name="product_id" value="<?php echo htmlspecialchars((string) $quickProduct['id'], ENT_QUOTES, 'UTF-8'); ?>">
              <input type="hidden" name="return_anchor" value="catalogue-electronique">
              <button type="submit" class="quick-btn">+ <?php echo htmlspecialchars((string) $quickProduct['name'], ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
          <?php endforeach; ?>
        </div>
      </div>

      <aside class="status-box">
        <div class="label">Fonctionnalites disponibles</div>
        <div class="value"><?php echo $availableCount; ?> / <?php echo count($actions); ?></div>
        <div style="margin-top:8px;font-size:13px;color:#34507d;">
          Email: <?php echo htmlspecialchars((string) $clientProfile['email'], ENT_QUOTES, 'UTF-8'); ?><br>
          Contact: <?php echo htmlspecialchars((string) $clientProfile['contact'], ENT_QUOTES, 'UTF-8'); ?><br>
          Ville: <?php echo htmlspecialchars((string) $clientProfile['ville'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="status-cart">
          Produits dans le panier: <strong><?php echo $cartTotalItems; ?></strong><br>
          Montant total: <strong><?php echo number_format((float) $cartTotalAmount, 0, ',', ' '); ?> FCFA</strong><br>
          <a href="#panier-express">Gerer mon panier rapide</a>
        </div>
      </aside>
    </section>

    <section class="stats">
      <article class="stat">
        <div class="name">Total commandes</div>
        <div class="num"><?php echo $stats['orders_total']; ?></div>
        <div class="sub">Toutes periodes</div>
      </article>

      <article class="stat">
        <div class="name">Commandes en attente</div>
        <div class="num"><?php echo $stats['orders_pending']; ?></div>
        <div class="sub">A traiter</div>
      </article>

      <article class="stat">
        <div class="name">Commandes livrees</div>
        <div class="num"><?php echo $stats['orders_delivered']; ?></div>
        <div class="sub">Finalisees</div>
      </article>

      <article class="stat">
        <div class="name">Montant paye</div>
        <div class="num"><?php echo number_format((float) $stats['amount_paid'], 0, ',', ' '); ?> FCFA</div>
        <div class="sub">Derniere commande: <?php echo $stats['last_order_date'] ? htmlspecialchars((string) $stats['last_order_date'], ENT_QUOTES, 'UTF-8') : '-'; ?></div>
      </article>
    </section>

    <section class="shop-grid">
      <article class="panel" id="catalogue-electronique">
        <h2 style="margin:0;">Produits a ajouter au panier</h2>
        <p style="margin:6px 0 0;color:#415378;">Catalogue electronique: 20 produits avec description, prix en FCFA et image JPG.</p>

        <div class="products-grid">
          <?php foreach ($productCatalog as $product): ?>
            <article class="product-card">
              <img
                class="product-image"
                src="<?php echo htmlspecialchars((string) $product['image'], ENT_QUOTES, 'UTF-8'); ?>"
                alt="<?php echo htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                onerror="this.src='../images/smartphone.jpg';"
              >
              <div class="product-body">
                <h3><?php echo htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="product-desc"><?php echo htmlspecialchars((string) $product['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="product-price"><?php echo number_format((float) $product['price'], 0, ',', ' '); ?> FCFA</div>
                <form method="post">
                  <input type="hidden" name="cart_action" value="add">
                  <input type="hidden" name="product_id" value="<?php echo htmlspecialchars((string) $product['id'], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="return_anchor" value="catalogue-electronique">
                  <button type="submit" class="btn-add">Ajouter au panier</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </article>

      <aside class="panel cart-panel" id="panier-express">
        <h3>Panier rapide</h3>
        <p style="margin:6px 0 0;color:#415378;font-size:13px;">Le panier affiche le prix de chaque produit, sa quantite et le total a payer.</p>

        <div class="cart-summary">
          <div class="cart-summary-line">
            <span>Produits selectionnes</span>
            <strong><?php echo $cartTotalItems; ?></strong>
          </div>
          <div class="cart-summary-line">
            <span>Articles differents</span>
            <strong><?php echo count($cartRows); ?></strong>
          </div>
          <div class="cart-summary-line">
            <span>Total a payer</span>
            <strong><?php echo number_format((float) $cartTotalAmount, 0, ',', ' '); ?> FCFA</strong>
          </div>
        </div>

        <?php if (!empty($cartRows)): ?>
          <div class="cart-items">
            <?php foreach ($cartRows as $cartRow): ?>
              <article class="cart-item">
                <img
                  class="cart-item-image"
                  src="<?php echo htmlspecialchars((string) $cartRow['image'], ENT_QUOTES, 'UTF-8'); ?>"
                  alt="<?php echo htmlspecialchars((string) $cartRow['name'], ENT_QUOTES, 'UTF-8'); ?>"
                  onerror="this.src='../images/smartphone.jpg';"
                >
                <div>
                  <p class="cart-item-name"><?php echo htmlspecialchars((string) $cartRow['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                  <div class="cart-item-meta">Prix unitaire: <?php echo number_format((float) $cartRow['price'], 0, ',', ' '); ?> FCFA</div>
                  <div class="cart-item-meta">Quantite: <?php echo (int) $cartRow['qty']; ?></div>
                  <div class="cart-item-total">Total ligne: <?php echo number_format((float) $cartRow['line_total'], 0, ',', ' '); ?> FCFA</div>

                  <div class="cart-controls">
                    <form method="post">
                      <input type="hidden" name="cart_action" value="dec">
                      <input type="hidden" name="product_id" value="<?php echo htmlspecialchars((string) $cartRow['key'], ENT_QUOTES, 'UTF-8'); ?>">
                      <input type="hidden" name="return_anchor" value="panier-express">
                      <button class="btn-small" type="submit">- 1</button>
                    </form>
                    <form method="post">
                      <input type="hidden" name="cart_action" value="inc">
                      <input type="hidden" name="product_id" value="<?php echo htmlspecialchars((string) $cartRow['key'], ENT_QUOTES, 'UTF-8'); ?>">
                      <input type="hidden" name="return_anchor" value="panier-express">
                      <button class="btn-small" type="submit">+ 1</button>
                    </form>
                    <form method="post">
                      <input type="hidden" name="cart_action" value="remove">
                      <input type="hidden" name="product_id" value="<?php echo htmlspecialchars((string) $cartRow['key'], ENT_QUOTES, 'UTF-8'); ?>">
                      <input type="hidden" name="return_anchor" value="panier-express">
                      <button class="btn-small danger" type="submit">Retirer</button>
                    </form>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <form method="post">
            <input type="hidden" name="cart_action" value="clear">
            <input type="hidden" name="return_anchor" value="panier-express">
            <button type="submit" class="btn-clear">Vider le panier rapide</button>
          </form>
          <form method="post">
            <input type="hidden" name="cart_action" value="place_order">
            <input type="hidden" name="return_anchor" value="panier-express">
            <button type="submit" class="btn-order">Passer commande</button>
          </form>
        <?php else: ?>
          <p class="empty">Aucun produit dans le panier pour le moment.</p>
        <?php endif; ?>

        <div class="cart-links">
          <a href="#panier-express">Panier</a>
          <a href="../client/panier.php">Voir le panier</a>
          <a href="../client/catalogue.php">Catalogue complet</a>
        </div>
      </aside>
    </section>

    <section class="panel">
      <h2 style="margin:0 0 14px;">Actions client</h2>
      <div class="actions-grid">
        <?php foreach ($actions as $action): ?>
          <?php $isAvailable = file_exists(__DIR__ . '/' . $action['href']); ?>
          <a class="action <?php echo $isAvailable ? '' : 'disabled'; ?>" href="<?php echo htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8'); ?>">
            <span class="tag <?php echo $isAvailable ? 'live' : 'todo'; ?>"><?php echo $isAvailable ? 'Disponible' : 'A implementer'; ?></span>
            <h3><?php echo htmlspecialchars($action['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p><?php echo htmlspecialchars($action['description'], ENT_QUOTES, 'UTF-8'); ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="tables">
      <article class="panel table-box">
        <h3>Dernieres commandes</h3>
        <?php if (!empty($recentOrders)): ?>
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $order): ?>
                <tr>
                  <td><?php echo (int) $order['id_commande']; ?></td>
                  <td><?php echo htmlspecialchars((string) $order['date_commande'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars((string) $order['statut'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="empty">Aucune commande enregistree pour le moment.</p>
        <?php endif; ?>
      </article>

      <article class="panel table-box">
        <h3>Derniers paiements</h3>
        <?php if (!empty($recentPayments)): ?>
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Montant</th>
                <th>Mode</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentPayments as $payment): ?>
                <tr>
                  <td><?php echo htmlspecialchars((string) $payment['date_paiement'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo number_format((float) $payment['montant'], 0, ',', ' '); ?> FCFA</td>
                  <td><?php echo htmlspecialchars((string) ($payment['mode'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars((string) ($payment['statut'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="empty">Aucun paiement enregistre pour ce compte.</p>
        <?php endif; ?>
      </article>
    </section>
  </main>
</body>
</html>
