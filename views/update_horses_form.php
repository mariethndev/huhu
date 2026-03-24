<?php
session_start();
require_once '../head.php';

// 🔐 CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'organisateur'
) {
    header("Location: ../views/homepage.php");
    exit;
}

if (!isset($horse)) {
    die("Aucune donnée fournie à la vue");
}

$imagePath = $imagePath ?? "/huhu/uploads/horses/horse_default.png";
?>

<div class="af-page">

    <div class="af-page-header">
        <h1 class="af-page-title">Modifier le cheval</h1>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="af-alert af-alert--success">Cheval mis à jour</div>
    <?php endif; ?>

    <div class="af-card">

        <form action="../controller/update_horses_ctrl.php" method="post" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="horse_id" value="<?= (int)$horse['id_horse'] ?>">

            <div class="af-grid-2">

                <div class="af-field af-field--full">
                    <img src="<?= htmlentities($imagePath, ENT_QUOTES, 'UTF-8') ?>" style="max-width:200px">
                    <input type="file" name="horse_image">
                </div>

                <div class="af-field">
                    <label>Nom *</label>
                    <input type="text" name="horse_name"
                           value="<?= htmlentities($horse['horse_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                </div>

                <div class="af-field">
                    <label>Sexe</label>
                    <select name="horse_sex">
                        <option value="M" <?= ($horse['horse_sex'] ?? '')=='M'?'selected':'' ?>>Mâle</option>
                        <option value="F" <?= ($horse['horse_sex'] ?? '')=='F'?'selected':'' ?>>Femelle</option>
                    </select>
                </div>

                <div class="af-field">
                    <label>Date</label>
                    <input type="date" name="horse_birthdate"
                           value="<?= htmlentities($horse['horse_birthdate'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="af-field">
                    <label>Race</label>
                    <input type="text" name="horse_breed"
                           value="<?= htmlentities($horse['horse_breed'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="af-field">
                    <label>Discipline</label>
                    <input type="text" name="horse_discipline"
                           value="<?= htmlentities($horse['horse_discipline'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="af-field">
                    <label>Robe</label>
                    <input type="text" name="horse_coat"
                           value="<?= htmlentities($horse['horse_coat'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="af-field">
                    <label>Taille</label>
                    <input type="number" name="horse_height"
                           value="<?= (int)($horse['horse_height'] ?? 0) ?>">
                </div>

                <div class="af-field">
                    <label>Poids</label>
                    <input type="number" name="horse_weight"
                           value="<?= (int)($horse['horse_weight'] ?? 0) ?>">
                </div>

                <div class="af-field af-field--full">
                    <label>Lieu</label>
                    <input type="text" name="horse_location"
                           value="<?= htmlentities($horse['horse_location'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="af-field">
                    <label>Père</label>
                    <input type="text" name="horse_father"
                           value="<?= htmlentities($horse['horse_father'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="af-field">
                    <label>Mère</label>
                    <input type="text" name="horse_mother"
                           value="<?= htmlentities($horse['horse_mother'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="af-field">
                    <label>ID</label>
                    <input type="text" name="horse_id_number"
                           value="<?= htmlentities($horse['horse_id_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="af-field">
                    <label>UELN</label>
                    <input type="text" name="horse_nb_ueln"
                           value="<?= htmlentities($horse['horse_nb_ueln'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="af-field">
                    <label>Statut</label>
                    <select name="horse_status">
                        <option value="disponible" <?= ($horse['horse_status'] ?? '')=='disponible'?'selected':'' ?>>Disponible</option>
                        <option value="indisponible" <?= ($horse['horse_status'] ?? '')=='indisponible'?'selected':'' ?>>Indisponible</option>
                    </select>
                </div>

                <div class="af-field">
                    <label>Prix</label>
                    <input type="number" name="auction_starting_price">
                </div>

                <div class="af-field af-field--full">
                    <label>Description</label>
                    <textarea name="horse_description"><?= htmlentities($horse['horse_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

            </div>

            <button type="submit">Enregistrer</button>

        </form>

    </div>
</div>

<?php require_once '../footer.php'; ?>