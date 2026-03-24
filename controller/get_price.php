<?php
session_start();
require_once "../model/config.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$horseId = (int)($data['horse_id'] ?? 0);

if ($horseId <= 0) {
    echo json_encode(["success" => false]);
    exit;
}

try {

    $stmtAuction = $pdo->prepare("
        SELECT id_auction, auction_starting_price
        FROM auctions
        WHERE horse_id_fk = ?
        LIMIT 1
    ");
    $stmtAuction->execute([$horseId]);
    $auction = $stmtAuction->fetch(PDO::FETCH_ASSOC);

    if (!$auction) {
        echo json_encode(["success" => false]);
        exit;
    }

    $auctionId = (int)$auction['id_auction'];

    $stmt = $pdo->prepare("
        SELECT bid_amount, user_id_fk
        FROM bids
        WHERE auction_id_fk = ?
        ORDER BY bid_amount DESC
        LIMIT 1
    ");
    $stmt->execute([$auctionId]);
    $bid = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($bid) {
        $price = (float)$bid['bid_amount'];
        $last  = (int)$bid['user_id_fk'];
    } else {
        $price = (float)$auction['auction_starting_price'];
        $last  = null;
    }

    $user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $hasBid = false;
    if ($user) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM bids 
            WHERE auction_id_fk = ? AND user_id_fk = ?
        ");
        $stmt->execute([$auctionId, $user]);
        $hasBid = $stmt->fetchColumn() > 0;
    }

    echo json_encode([
        "success" => true,
        "price" => $price,
        "last_bidder" => $last,
        "current_user" => $user,
        "has_bid" => $hasBid
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}