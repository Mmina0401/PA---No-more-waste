<?php
session_start();

require_once __DIR__ . "/../includes/api.php";
require_once __DIR__ . "/../includes/lang.php";

$messageSucces = null;
$messageErreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reponse = API::post("/api/public/candidature-benevole", [
        "nom"          => trim($_POST["nom"] ?? ""),
        "prenom"       => trim($_POST["prenom"] ?? ""),
        "email"        => trim($_POST["email"] ?? ""),
        "mot_de_passe" => $_POST["mot_de_passe"] ?? "",
        "telephone"    => trim($_POST["telephone"] ?? ""),
        "ville"        => trim($_POST["ville"] ?? ""),
    ]);

    if (isset($reponse["message"])) {
        $messageSucces = t("volunteer_success");
    } else {
        $messageErreur = t("volunteer_error");
    }
}

include __DIR__ . "/entete.php";
?>

<div class="container py-5" style="max-width: 560px;">

<h1 class="mb-3"><?= t("volunteer_title") ?></h1>
<p class="texte-secondaire mb-4"><?= t("volunteer_intro") ?></p>

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
        <label class="form-label"><?= t("choose_password") ?></label>
        <input type="password" name="mot_de_passe" class="form-control" required>
        <div class="form-text"><?= t("password_help") ?></div>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("phone") ?></label>
        <input type="text" name="telephone" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("city") ?></label>
        <input type="text" name="ville" class="form-control">
    </div>

    <button
        type="submit"
        class="btn w-100"
        style="background: var(--vert-feuille); color: var(--anthracite); border-radius: 14px; font-weight: 600; border: none; padding: 10px 20px;"
    >
        <?= t("volunteer_submit") ?>
    </button>

</form>
</div>
<?php endif; ?>

</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>