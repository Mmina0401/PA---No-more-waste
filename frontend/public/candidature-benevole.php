<?php
session_start();
require_once __DIR__ . "/../includes/api.php";

$messageSucces = null;
$messageErreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reponse = API::post("/api/public/candidature-benevole", [
        "nom"          => trim($_POST["nom"]),
        "prenom"       => trim($_POST["prenom"]),
        "email"        => trim($_POST["email"]),
        "mot_de_passe" => $_POST["mot_de_passe"],
        "telephone"    => trim($_POST["telephone"]),
        "ville"        => trim($_POST["ville"]),
    ]);

    if (isset($reponse["message"])) {
        $messageSucces = "Merci ! Votre candidature a bien été enregistrée. Un responsable va l'examiner avant que vous puissiez vous connecter.";
    } else {
        $messageErreur = "Impossible d'enregistrer votre candidature (cet email est peut-être déjà utilisé).";
    }
}

include __DIR__ . "/../includes/header.php";
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
<div class="container-fluid">
<a class="navbar-brand fw-bold" href="services.php">
<i class="fa-solid fa-leaf"></i> No More Waste
</a>
<a href="/login.php" class="btn btn-outline-light btn-sm">Espace membre</a>
</div>
</nav>

<div class="container py-5" style="max-width: 560px;">

<h1 class="mb-3">Devenir bénévole</h1>
<p class="text-muted mb-4">Proposez vos services à l'association, sans engagement immédiat.</p>

<?php if ($messageSucces): ?>
    <div class="alert alert-success"><?= htmlspecialchars($messageSucces) ?></div>
<?php elseif ($messageErreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($messageErreur) ?></div>
<?php endif; ?>

<?php if (!$messageSucces): ?>
<form method="post">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Prénom</label>
            <input type="text" name="prenom" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Choisissez un mot de passe</label>
        <input type="password" name="mot_de_passe" class="form-control" required>
        <div class="form-text">Vous vous en servirez pour vous connecter une fois votre candidature validée.</div>
    </div>

    <div class="mb-3">
        <label class="form-label">Téléphone</label>
        <input type="text" name="telephone" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Ville</label>
        <input type="text" name="ville" class="form-control">
    </div>

    <button type="submit" class="btn btn-success w-100">Envoyer ma candidature</button>
</form>
<?php endif; ?>

</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>