<?php
require_once "../model/config.php";

$auctions   = [];
$enCours    = [];
$terminees  = [];
$annulees   = [];

try {

    $stmt = $pdo->query("
        SELECT *
        FROM auctions
        ORDER BY start_date DESC
    ");

    $auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($auctions as &$auction) {

        if (empty($auction['horse_id'])) {
            continue;
        }

        $horseId = $auction['horse_id'];

         $stmtHorse = $pdo->prepare("
            SELECT name
            FROM horses
            WHERE id = ?
        ");
        $stmtHorse->execute([$horseId]);
        $horse = $stmtHorse->fetch(PDO::FETCH_ASSOC);

        $auction['horse_name'] = $horse['name'] ?? '—';

         $stmtBid = $pdo->prepare("
            SELECT amount, user_id
            FROM bids
            WHERE auction_id = ?
            ORDER BY amount DESC
            LIMIT 1
        ");
        $stmtBid->execute([$auction['id']]);
        $lastBid = $stmtBid->fetch(PDO::FETCH_ASSOC);

        if ($lastBid) {
            $auction['last_bid']    = $lastBid['amount'];
            $auction['last_bidder'] = $lastBid['user_id'];
        } else {
            $auction['last_bid']    = $auction['starting_price'];
            $auction['last_bidder'] = null;
        }

         if (!empty($auction['end_date']) &&
            strtotime($auction['end_date']) < time()) {

            $auction['status'] = 'ended';

        } elseif ($auction['status'] === 'cancelled') {

            $auction['status'] = 'cancelled';

        } else {

            $auction['status'] = 'active';
        }
    }

     foreach ($auctions as $auction) {

        if ($auction['status'] === "active") {
            $enCours[] = $auction;

        } elseif ($auction['status'] === "ended") {
            $terminees[] = $auction;

        } else {
            $annulees[] = $auction;
        }
    }

} catch (PDOException $e) {

    $auctions   = [];
    $enCours    = [];
    $terminees  = [];
    $annulees   = [];
}