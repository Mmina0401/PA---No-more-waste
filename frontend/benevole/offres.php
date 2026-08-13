<?php
session_start();

require_once __DIR__ . "/../includes/auth.php";
exigerRole("BENEVOLE");

require_once __DIR__ . "/../includes/api.php";

$utilisateur = $_SESSION["utilisateur"] ?? [];
$prenom = $utilisateur["prenom"] ?? "";
$nom = $utilisateur["nom"] ?? "";

$messageSucces = "";
$messageErreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idOffre = (int) ($_POST["id_offre"] ?? 0);
    $idJours = $_POST["id_jours"] ?? [];

    if (!is_array($idJours)) {
        $idJours = [];
    }

    $idJours = array_values(array_unique(array_filter(
        array_map("intval", $idJours),
        fn($id) => $id > 0
    )));

    if ($idOffre <= 0) {
        $messageErreur = "Offre invalide.";
    } elseif (empty($idJours)) {
        $messageErreur = "Sélectionnez au moins une journée.";
    } else {
        $reponse = API::post("/api/offres-benevoles/repondre", [
            "id_offre" => $idOffre,
            "id_jours" => $idJours
        ]);

        if (is_array($reponse) && isset($reponse["message"])) {
            $messageSucces = "Vos disponibilités ont bien été enregistrées.";
        } else {
            $messageErreur = "Impossible d'enregistrer vos disponibilités.";
        }
    }
}

$offres = API::get("/api/offres-benevoles");
if (!is_array($offres)) {
    $offres = [];
}

$mesReponses = API::get("/api/offres-benevoles/mes-reponses");
if (!is_array($mesReponses)) {
    $mesReponses = [];
}

$reponsesParOffre = [];

foreach ($mesReponses as $reponse) {
    $idOffreReponse = (int) ($reponse["id_offre"] ?? 0);

    if ($idOffreReponse <= 0) {
        continue;
    }

    $jours = $reponse["id_jours"] ?? [];

    if (!is_array($jours)) {
        $jours = [];
    }

    $reponsesParOffre[$idOffreReponse] = array_map("intval", $jours);
}

$offresOuvertes = [];
$aujourdhui = date("Y-m-d");

foreach ($offres as $offre) {
    if (($offre["statut"] ?? "") !== "OUVERTE") {
        continue;
    }

    $joursValides = [];

    foreach (($offre["jours"] ?? []) as $jour) {
        $dateJour = $jour["date_jour"] ?? "";

        if ($dateJour !== "" && $dateJour >= $aujourdhui) {
            $joursValides[] = $jour;
        }
    }

    if (empty($joursValides)) {
        continue;
    }

    $offre["jours"] = $joursValides;
    $offresOuvertes[] = $offre;
}

$nbOffres = count($offresOuvertes);

include __DIR__ . "/../includes/header.php";
?>

<style>
body {
    background: #f4f9f3;
}

.benevole-navbar {
    background: #2E7D32;
    min-height: 68px;
}

.benevole-navbar .navbar-brand {
    color: white !important;
    font-weight: 700;
    font-size: 21px;
}

.benevole-navbar .nav-link {
    color: rgba(255,255,255,.9) !important;
    border-radius: 10px;
    padding: 8px 14px !important;
}

.benevole-navbar .nav-link:hover,
.benevole-navbar .nav-link.active {
    background: rgba(255,255,255,.15);
    color: white !important;
}

.benevole-user {
    color: white;
    font-size: 14px;
}

.offres-container {
    max-width: 1250px;
}

.offre-card {
    border: 0;
    border-radius: 18px;
    box-shadow: 0 5px 20px rgba(38,50,56,.08);
    overflow: hidden;
    transition: .15s ease;
}

.offre-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(38,50,56,.12);
}

.offre-type {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 999px;
    background: #e8f5e9;
    color: #2E7D32;
    font-size: 13px;
    font-weight: 700;
}

.horaire-box {
    background: #f4f9f3;
    border-radius: 12px;
    padding: 12px 15px;
}

.jour-option {
    display: block;
    border: 1px solid #dee5df;
    border-radius: 12px;
    padding: 13px 15px;
    margin-bottom: 9px;
    cursor: pointer;
    transition: .15s ease;
}

.jour-option:hover {
    border-color: #2E7D32;
    background: #f4f9f3;
}

.jour-option:has(input:checked) {
    border-color: #2E7D32;
    background: #e8f5e9;
}

.jour-option input {
    margin-right: 10px;
    accent-color: #2E7D32;
}

.reponse-enregistree {
    background: #e8f5e9;
    color: #256629;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 14px;
    font-weight: 600;
}

.btn-nmw {
    background: #2E7D32;
    border-color: #2E7D32;
    color: white;
}

.btn-nmw:hover {
    background: #256629;
    border-color: #256629;
    color: white;
}

.empty-box {
    border: 1px dashed #cfd8dc;
    border-radius: 18px;
    background: white;
}
</style>

<nav class="navbar navbar-expand-lg benevole-navbar shadow-sm">
<div class="container-fluid px-lg-4">
<a class="navbar-brand" href="/benevole/dashboard.php">
    <i class="fa-solid fa-leaf me-2"></i>No More Waste
</a>

<button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarBenevole">
    <span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="navbarBenevole">
<ul class="navbar-nav me-auto mb-2 mb-lg-0">
    <li class="nav-item">
        <a class="nav-link" href="/benevole/dashboard.php">
            <i class="fa-solid fa-house me-1"></i> Tableau de bord
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link active" href="/benevole/offres.php">
            <i class="fa-solid fa-hand-holding-heart me-1"></i> Offres disponibles
            <?php if ($nbOffres > 0): ?>
                <span class="badge bg-warning text-dark ms-1"><?= $nbOffres ?></span>
            <?php endif; ?>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="/benevole/planning.php">
            <i class="fa-solid fa-calendar-days me-1"></i> Mon planning
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="/benevole/profil.php">
            <i class="fa-solid fa-user me-1"></i> Mon profil
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="/public/accueil.php">
            <i class="fa-solid fa-globe me-1"></i> Site public
        </a>
    </li>
</ul>

<div class="d-flex align-items-center gap-3">
    <div class="benevole-user d-none d-lg-block">
        <i class="fa-solid fa-circle-user me-1"></i>
        <?= htmlspecialchars(trim($prenom . " " . $nom)) ?>
    </div>

    <a href="/logout.php" class="btn btn-outline-light btn-sm">
        <i class="fa-solid fa-right-from-bracket me-1"></i> Déconnexion
    </a>
</div>
</div>
</div>
</nav>

<main class="container offres-container py-5">

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-2">Offres disponibles</h1>
        <p class="text-muted mb-0">
            Consultez les missions qui recherchent des bénévoles et indiquez vos disponibilités.
        </p>
    </div>

    <span class="badge bg-success fs-6">
        <?= $nbOffres ?> <?= $nbOffres > 1 ? "offres disponibles" : "offre disponible" ?>
    </span>
</div>

<?php if ($messageSucces): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check me-2"></i>
        <?= htmlspecialchars($messageSucces) ?>
    </div>
<?php endif; ?>

<?php if ($messageErreur): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        <?= htmlspecialchars($messageErreur) ?>
    </div>
<?php endif; ?>

<?php if (empty($offresOuvertes)): ?>

<div class="empty-box text-center p-5">
    <i class="fa-solid fa-hand-holding-heart fa-3x mb-3" style="color:#7BC96F;"></i>
    <h4>Aucune offre disponible</h4>
    <p class="text-muted mb-0">
        Il n'y a actuellement aucune mission nécessitant des bénévoles.
    </p>
</div>

<?php else: ?>

<div class="row g-4">

<?php foreach ($offresOuvertes as $offre): ?>

<?php
$idOffre = (int) ($offre["id_offre"] ?? 0);
$joursSelectionnes = $reponsesParOffre[$idOffre] ?? [];
$aDejaRepondu = !empty($joursSelectionnes);
?>

<div class="col-lg-6">
<div class="card offre-card h-100">
<div class="card-body p-4">

<div class="d-flex justify-content-between align-items-start gap-2 mb-3">
    <span class="offre-type">
        <?php if (($offre["type_evenement"] ?? "") === "COLLECTE"): ?>
            <i class="fa-solid fa-truck me-1"></i> Collecte
        <?php else: ?>
            <i class="fa-solid fa-handshake me-1"></i> Service
        <?php endif; ?>
    </span>

    <span class="badge bg-success">Ouverte</span>
</div>

<h4 class="mb-2">
    <?= htmlspecialchars($offre["titre"] ?? "Offre bénévole") ?>
</h4>

<?php if (!empty($offre["description"])): ?>
    <p class="text-muted">
        <?= nl2br(htmlspecialchars($offre["description"])) ?>
    </p>
<?php endif; ?>

<div class="horaire-box mb-4">
    <i class="fa-solid fa-clock me-2" style="color:#2E7D32;"></i>
    <strong>Horaires :</strong>
    <?= htmlspecialchars($offre["heure_debut"] ?? "") ?>
    -
    <?= htmlspecialchars($offre["heure_fin"] ?? "") ?>
</div>

<?php if ($aDejaRepondu): ?>
    <div class="reponse-enregistree mb-3">
        <i class="fa-solid fa-circle-check me-1"></i>
        Vous avez déjà indiqué vos disponibilités pour cette offre.
    </div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="id_offre" value="<?= $idOffre ?>">

<h6 class="fw-bold mb-2">
    <?= $aDejaRepondu ? "Modifier mes disponibilités" : "Choisir mes disponibilités" ?>
</h6>

<p class="text-muted small">
    Cochez uniquement les journées pendant lesquelles vous pouvez réellement participer.
</p>

<?php foreach ($offre["jours"] as $jour): ?>

<?php
$idJour = (int) ($jour["id_jour"] ?? 0);
$dateBrute = $jour["date_jour"] ?? "";
$dateAffichee = $dateBrute;

if ($dateBrute !== "") {
    $timestamp = strtotime($dateBrute);

    if ($timestamp !== false) {
        $dateAffichee = date("d/m/Y", $timestamp);
    }
}

$estSelectionne = in_array($idJour, $joursSelectionnes, true);
?>

<label class="jour-option">
    <input
        type="checkbox"
        name="id_jours[]"
        value="<?= $idJour ?>"
        <?= $estSelectionne ? "checked" : "" ?>
    >

    <i class="fa-solid fa-calendar-day me-2"></i>

    <strong><?= htmlspecialchars($dateAffichee) ?></strong>

    <span class="text-muted ms-2">
        <?= htmlspecialchars($offre["heure_debut"] ?? "") ?>
        -
        <?= htmlspecialchars($offre["heure_fin"] ?? "") ?>
    </span>
</label>

<?php endforeach; ?>

<button type="submit" class="btn btn-nmw w-100 mt-3">
    <?php if ($aDejaRepondu): ?>
        <i class="fa-solid fa-pen me-1"></i> Modifier mes disponibilités
    <?php else: ?>
        <i class="fa-solid fa-check me-1"></i> Envoyer mes disponibilités
    <?php endif; ?>
</button>

</form>
</div>
</div>
</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>