<?php
session_start();

require_once __DIR__ . "/../includes/api.php";
require_once __DIR__ . "/../includes/lang.php";

$messageSucces = null;
$messageErreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reponse = API::post("/api/public/demande-collecte", [
        "nom"           => trim($_POST["nom"] ?? ""),
        "prenom"        => trim($_POST["prenom"] ?? ""),
        "email"         => trim($_POST["email"] ?? ""),
        "adresse"       => trim($_POST["adresse"] ?? ""),
        "ville"         => trim($_POST["ville"] ?? ""),
        "code_postal"   => trim($_POST["code_postal"] ?? ""),
        "date_collecte" => $_POST["date_collecte"] ?? "",
        "heure_debut"   => $_POST["heure_debut"] ?? "",
        "heure_fin"     => $_POST["heure_fin"] ?? "",
        "commentaire"   => trim($_POST["commentaire"] ?? ""),
    ]);

    if (isset($reponse["message"])) {
        $messageSucces = t("collection_success");
    } else {
        $messageErreur = t("collection_error");
    }
}

include __DIR__ . "/entete.php";
?>

<div class="container py-5" style="max-width: 640px;">

<h1 class="mb-3"><?= t("collection_title") ?></h1>
<p class="texte-secondaire mb-4"><?= t("collection_intro") ?></p>

<?php if ($messageSucces): ?>
    <div class="alert alert-success"><?= htmlspecialchars($messageSucces, ENT_QUOTES, "UTF-8") ?></div>
<?php elseif ($messageErreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($messageErreur, ENT_QUOTES, "UTF-8") ?></div>
<?php endif; ?>

<?php if (!$messageSucces): ?>
<div class="carte-nmw p-4">
<form method="post">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label"><?= t("first_name") ?></label>
            <input type="text" name="prenom" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label"><?= t("last_name") ?></label>
            <input type="text" name="nom" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("email") ?></label>
        <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("collection_address") ?></label>
        <input type="text" name="adresse" class="form-control" required>
    </div>

    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label"><?= t("city") ?></label>
            <input type="text" name="ville" class="form-control" required>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label"><?= t("postal_code") ?></label>
            <input type="text" name="code_postal" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("preferred_date") ?></label>
        <input type="date" name="date_collecte" class="form-control" required>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label"><?= t("time_from") ?></label>
            <input type="time" name="heure_debut" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label"><?= t("time_until") ?></label>
            <input type="time" name="heure_fin" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("comment_optional") ?></label>
        <textarea name="commentaire" class="form-control" rows="3"></textarea>
    </div>

    <button type="submit" class="btn btn-secondaire w-100">
        <?= t("collection_submit") ?>
    </button>

</form>
</div>
<?php endif; ?>

</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>