<?php
session_start();
require_once __DIR__ . "/../includes/api.php";

$messageSucces = null;
$messageErreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reponse = API::post("/api/public/inscription", [
        "nom"        => trim($_POST["nom"]),
        "prenom"     => trim($_POST["prenom"]),
        "email"      => trim($_POST["email"]),
        "id_service" => (int) $_POST["id_service"],
    ]);

    if (isset($reponse["message"])) {
        $messageSucces = "Inscription enregistrée ! Vous recevrez une confirmation.";
    } else {
        $messageErreur = "Impossible de vous inscrire (service complet, ou vous êtes déjà inscrit).";
    }
}

$services = API::get("/api/public/services");
if (!is_array($services)) $services = [];

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

<div class="container py-5">

<h1 class="mb-3">Nos services solidaires</h1>
<p class="text-muted mb-4">Découvrez les prochains ateliers et inscrivez-vous, sans créer de compte.</p>

<?php if ($messageSucces): ?>
    <div class="alert alert-success"><?= htmlspecialchars($messageSucces) ?></div>
<?php endif; ?>
<?php if ($messageErreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($messageErreur) ?></div>
<?php endif; ?>

<?php if (empty($services)): ?>
    <div class="alert alert-light border">Aucun service ouvert pour le moment, revenez bientôt !</div>
<?php else: ?>

<div class="row g-4">
<?php foreach ($services as $s): ?>
<div class="col-md-4">
<div class="card h-100">
<div class="card-body">
<h5 class="card-title"><?= htmlspecialchars($s["nom"]) ?></h5>
<p class="card-text text-muted"><?= htmlspecialchars($s["description"] ?? "") ?></p>
<p class="small mb-1">
<i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($s["lieu"] ?? "Lieu à confirmer") ?>
</p>
<p class="small mb-3">
<i class="fa-solid fa-calendar-days"></i> <?= htmlspecialchars($s["date_service"] ?? "") ?>
<?= substr($s["heure_debut"] ?? "", 0, 5) ?> - <?= substr($s["heure_fin"] ?? "", 0, 5) ?>
</p>
<button type="button" class="btn btn-success w-100" data-bs-toggle="modal"
data-bs-target="#modalInscription" onclick="choisirService(<?= $s["id_service"] ?>, '<?= htmlspecialchars($s["nom"], ENT_QUOTES) ?>')">
S'inscrire
</button>
</div>
</div>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

</div>

<div class="modal fade" id="modalInscription" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="post">
<div class="modal-header">
<h5 class="modal-title">S'inscrire à <span id="nomServiceChoisi"></span></h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" name="id_service" id="idServiceChoisi">
<div class="mb-3">
<label class="form-label">Prénom</label>
<input type="text" name="prenom" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Nom</label>
<input type="text" name="nom" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" required>
</div>
</div>
<div class="modal-footer">
<button type="submit" class="btn btn-success">Confirmer l'inscription</button>
</div>
</form>
</div>
</div>
</div>

<script>
function choisirService(id, nom) {
    document.getElementById("idServiceChoisi").value = id;
    document.getElementById("nomServiceChoisi").textContent = nom;
}
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>