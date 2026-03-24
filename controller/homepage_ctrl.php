<?php
require_once "../model/config.php";

$horses = [];

try {

    $stmt = $pdo->prepare("
        SELECT *
        FROM auctions
        WHERE auction_status = ?
        ORDER BY auction_start_date DESC
    ");
    $stmt->execute(['disponible']);
    $auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($auctions as $auction) {

        $auctionId = (int)$auction['id_auction'];
        $horseId   = (int)$auction['horse_id_fk'];

        $stmtHorse = $pdo->prepare("
            SELECT *
            FROM horses
            WHERE id_horse = ?
            AND horse_is_deleted = 0
        ");
        $stmtHorse->execute([$horseId]);
        $horse = $stmtHorse->fetch(PDO::FETCH_ASSOC);

        if (!$horse) continue;

        $stmtPrice = $pdo->prepare("
            SELECT MAX(bid_amount)
            FROM bids
            WHERE auction_id_fk = ?
        ");
        $stmtPrice->execute([$auctionId]);
        $lastBid = $stmtPrice->fetchColumn();

        $currentPrice = ($lastBid !== null)
            ? (float)$lastBid
            : (float)$auction['auction_starting_price'];

        $horse['current_price'] = $currentPrice;
        $horse['auction_start_date'] = $auction['auction_start_date'];
        $horse['auction_end_date']   = $auction['auction_end_date'];
        $horse['id_auction']         = $auctionId;

        $horses[] = $horse;
    }

} catch (PDOException $e) {
    echo $e->getMessage();
    $horses = [];
}

$count = count($horses);