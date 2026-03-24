<?php
session_start();
require_once "../model/config.php";

if (($_SESSION['role'] ?? '') !== 'organisateur') {
    header("Location: ../views/homepage.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/organisateur_auctions.php");
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header("Location: ../views/organisateur_auctions.php");
    exit;
}

$id = (int)($_POST['auction_id'] ?? 0);

if ($id <= 0) {
    header("Location: ../views/organisateur_auctions.php");
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT *
        FROM auctions
        WHERE id_auction = ?
    ");
    $stmt->execute([$id]);
    $auction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$auction) {
        header("Location: ../views/organisateur_auctions.php");
        exit;
    }

    $stmtBid = $pdo->prepare("
        SELECT user_id_fk, bid_amount
        FROM bids
        WHERE auction_id_fk = ?
        ORDER BY bid_amount DESC
        LIMIT 1
    ");
    $stmtBid->execute([$id]);
    $bestBid = $stmtBid->fetch(PDO::FETCH_ASSOC);

    $winnerId  = $bestBid['user_id_fk'] ?? null;
    $finalPrice = $bestBid['bid_amount'] ?? $auction['auction_starting_price'];

    $stmtUpdate = $pdo->prepare("
        UPDATE auctions
        SET auction_status = 'terminé',
            auction_winner_id = ?,
            auction_final_price = ?
        WHERE id_auction = ?
    ");
    $stmtUpdate->execute([
        $winnerId,
        $finalPrice,
        $id
    ]);

    header("Location: ../views/organisateur_auctions.php");
    exit;

} catch (PDOException $e) {
    echo $e->getMessage();
    header("Location: ../views/organisateur_auctions.php");
    exit;
}