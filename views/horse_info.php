<?php
session_start();

// 🔐 CSRF (vue uniquement)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../controller/horse_info_ctrl.php';
require_once '../head.php';

$horse = $horse ?? [];
$auction = $auction ?? [];
$userLogged = $userLogged ?? false;

$auction['is_active'] = $auction['is_active'] ?? false;
$auction['is_last_user'] = $auction['is_last_user'] ?? false;

$imagePath = !empty($horse['horse_image'])
    ? "/huhu/huhu_linux/uploads/horses/" . htmlentities($horse['horse_image'], ENT_QUOTES, 'UTF-8')
    : "/huhu/huhu_linux/uploads/horses/horse_default.png";
?>

<div class="container-info-horse">

    <div class="left-card-horse">

        <h1 class="horse-title">
            Fiche de <?= htmlentities($horse['horse_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <div class="image-horse-info">
            <img src="<?= $imagePath ?>" alt="Photo du cheval">
        </div>

        <div class="horse-details-container">
            <a href="/huhu/huhu_linux/views/buy_a_horse.php" class="back-horse-info">
                RETOUR
            </a>
        </div>
    </div>

    <div class="right-card-horse">

        <div class="infos">

            <div class="card-info">
                <h3>DÉTAILS</h3>
                <p>Numéro : <?= htmlentities($horse['horse_id_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                <p>Naissance :
                    <?= !empty($horse['horse_birthdate'])
                        ? date('d/m/Y', strtotime($horse['horse_birthdate']))
                        : '—' ?>
                </p>

                <p>Discipline :
                    <strong><?= htmlentities($horse['horse_discipline'] ?? '—', ENT_QUOTES, 'UTF-8') ?></strong>
                </p>

                <p>Lieu : <?= htmlentities($horse['horse_location'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                <p>Date d'enregistrement :
                    <?= !empty($horse['horse_register_date'])
                        ? date('d/m/Y', strtotime($horse['horse_register_date']))
                        : '—' ?>
                </p>
            </div>

            <div class="card-info">
                <h3>MORPHOLOGIE</h3>
                <p>Robe : <?= htmlentities($horse['horse_coat'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                <p>Taille :
                    <?= !empty($horse['horse_height'])
                        ? $horse['horse_height'] . ' cm'
                        : 'NC' ?>
                </p>
                <p>Poids :
                    <?= !empty($horse['horse_weight'])
                        ? $horse['horse_weight'] . ' kg'
                        : 'NC' ?>
                </p>
                <p>Sexe : <?= htmlentities($horse['horse_sex'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="card-info">
                <h3>RACE</h3>

                <p><?= htmlentities($horse['horse_breed'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                <p>Numéro UELN : <?= htmlentities($horse['horse_nb_ueln'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="card-info">
                <h3>PARENTS</h3>

                <p>Mère : <?= htmlentities($horse['horse_mother'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                <p>Père : <?= htmlentities($horse['horse_father'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="card-info">
                <h3>DESCRIPTION</h3>
                <p>
                    <?php
                    $desc = trim($horse['horse_description'] ?? '');

                    if ($desc == '' || $desc == '...') {
                        echo "Aucune description disponible.";
                    } else {
                        echo nl2br(htmlentities($desc, ENT_QUOTES, 'UTF-8'));
                    }
                    ?>
                </p>
            </div>

            <div class="card-info">
                <h3>ENCHÈRE</h3>

                <p>
                    Statut :
                    <span class="badge <?= htmlentities($auction['badge_class'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlentities($auction['status_label'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </p>

                <p>
                    Prix actuel :
                    <strong>
                        <span class="live-price" data-auction-id="<?= (int)($auction['id_auction'] ?? 0) ?>">
                            <?= number_format($auction['current_price'] ?? $auction['starting_price'] ?? 0, 0, ',', ' ') ?> €
                        </span>
                    </strong>
                </p>

                <p class="voters-info btn btn-secondary p-2">
                    <?= (int)($auction['participants'] ?? 0) ?> participant(s)
                </p>

            </div>
        </div>

        <div class="cta-horse-info">
            <?php if (!$userLogged): ?>

                <p>Inscription requise pour participer.</p>
                <a href="/huhu/huhu_linux/views/register_form.php" class="btn-horse-info">
                    S'inscrire
                </a>

            <?php else: ?>

                <p id="bidMessage"
                   data-is-last-user="<?= $auction['is_last_user'] ? '1' : '0' ?>">
                </p>

                <button type="button" class="btn-bid"
                    data-price="<?= $auction['current_price'] ?? $auction['starting_price'] ?? 0 ?>"
                    data-user-bid="<?= $auction['my_last_bid'] ?? 0 ?>"
                    data-horse-name="<?= htmlentities($horse['horse_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    data-horse-id="<?= (int)($horse['id_horse'] ?? 0) ?>"
                    data-image="<?= $imagePath ?>"
                    <?= $auction['is_active'] ? '' : 'disabled' ?>>
                    Faire une offre
                </button>

            <?php endif; ?>
        </div>
    </div>
</div>

<div id="bidModal" class="custom-modal hidden">

    <div class="modal-card">

        <div class="modal-image">
            <img src="" alt="">
            <span class="modal-close">&times;</span>
        </div>

        <div class="modal-body">

            <h2 id="modalHorseName"></h2>

            <p class="modal-price">
                Prix actuel : <strong id="modalCurrentPrice"></strong>
            </p>

            <form action="/huhu/controller/bid_ctrl.php" method="POST">

                <!-- 🔐 CSRF -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <input type="hidden" name="horse_id" id="modalHorseId">

                <div class="bid-input">
                    <button type="button" id="bidMinus">−</button>
                    <input type="number" name="bid_amount" id="bidAmount">
                    <button type="button" id="bidPlus">+</button>
                </div>

                <button class="btn-consult" type="submit">
                    Enchérir
                </button>

            </form>
        </div>
    </div>
</div>

<?php require_once '../footer.php'; ?>