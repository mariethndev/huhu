<?php
session_start();

// 🔐 CSRF (vue uniquement)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (($_SESSION['role'] ?? '') !== 'organisateur') {
    header("Location: homepage.php");
    exit;
}

require_once '../controller/update_auction.php';
require_once '../head.php';
?>

<div class="af-page">

    <div class="af-page-header">
        <h1 class="af-page-title">Modifier l'enchère</h1>
        <p class="af-page-subtitle">Gérez les paramètres de l'enchère.</p>
    </div>

    <div class="af-card">

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="af-alert af-alert--success">
                Modification enregistrée avec succès.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'danger'): ?>
            <div class="af-alert af-alert--danger">
                Une erreur est survenue.
            </div>
        <?php endif; ?>

        <!-- 🔧 FORM UPDATE -->
        <form method="POST" action="../controller/update_auction.php">

            <!-- 🔐 CSRF -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <input type="hidden" name="auction_id" value="<?= (int)$auction['id_auction'] ?>">

            <div class="af-field">

                <label class="af-label">Date de fin</label>

                <input
                    type="date"
                    name="auction_end_date"
                    class="af-input"
                    value="<?= htmlentities($dateValue, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

            </div>

            <div class="af-field">

                <label class="af-label">Statut</label>

                <select name="auction_status" class="af-select">

                    <option value="disponible"
                        <?= $auction['auction_status'] == 'disponible' ? 'selected' : '' ?>>
                        Disponible
                    </option>

                    <option value="terminé"
                        <?= $auction['auction_status'] == 'terminé' ? 'selected' : '' ?>>
                        Terminé
                    </option>

                    <option value="annulé"
                        <?= $auction['auction_status'] == 'annulé' ? 'selected' : '' ?>>
                        Annulé
                    </option>

                </select>

            </div>

            <div class="af-footer">
                <button type="submit" class="btn btn-dark btn-md">
                    Enregistrer
                </button>
            </div>

        </form>

        <div class="af-divider"></div>

        <!-- 🔧 FORM CLOSE -->
        <form method="POST" action="../controller/close_auction.php">

            <!-- 🔐 CSRF -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <input type="hidden" name="auction_id" value="<?= (int)$auction['id_auction'] ?>">

            <div class="af-footer">

                <button type="submit" class="btn btn-secondary btn-md">
                    Clôturer maintenant
                </button>

            </div>

        </form>

    </div>

</div>

<?php require_once '../footer.php'; ?>