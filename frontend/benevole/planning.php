<?php
session_start();

require_once __DIR__ . "/../includes/auth.php";
exigerRole("BENEVOLE");

require_once __DIR__ . "/../includes/api.php";

$utilisateur = $_SESSION["utilisateur"] ?? [];

$messageSucces = $_GET["success"] ?? "";
$messageErreur = $_GET["error"] ?? "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "annuler_collecte"
) {
    $idCollecte = (int) ($_POST["id_collecte"] ?? 0);

    if ($idCollecte <= 0) {
        header(
            "Location: /benevole/planning.php?error=" .
            urlencode("Collecte invalide.")
        );
        exit;
    }

    $resultat = API::post(
        "/api/benevole/collecte/annuler",
        [
            "id_collecte" => $idCollecte
        ]
    );

    if (
        is_array($resultat) &&
        isset($resultat["message"])
    ) {
        header(
            "Location: /benevole/planning.php?success=" .
            urlencode("Votre participation à la collecte a été annulée.")
        );
        exit;
    }

    header(
        "Location: /benevole/planning.php?error=" .
        urlencode("Impossible d'annuler votre participation à cette collecte.")
    );
    exit;
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "annuler_service"
) {
    $idService = (int) ($_POST["id_service"] ?? 0);

    if ($idService <= 0) {
        header(
            "Location: /benevole/planning.php?error=" .
            urlencode("Service invalide.")
        );
        exit;
    }

    $resultat = API::post(
        "/api/benevole/service/annuler",
        [
            "id_service" => $idService
        ]
    );

    if (
        is_array($resultat) &&
        isset($resultat["message"])
    ) {
        header(
            "Location: /benevole/planning.php?success=" .
            urlencode("Votre participation au service a été annulée.")
        );
        exit;
    }

    header(
        "Location: /benevole/planning.php?error=" .
        urlencode("Impossible d'annuler votre participation à ce service.")
    );
    exit;
}

$planning = API::get("/api/benevole/planning");

if (!is_array($planning)) {
    $planning = [];
}

$collectes = $planning["collectes"] ?? [];
$services = $planning["services"] ?? [];

if (!is_array($collectes)) {
    $collectes = [];
}

if (!is_array($services)) {
    $services = [];
}

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

.planning-container {
    max-width: 1250px;
}

.planning-card {
    border: 0;
    border-radius: 18px;
    box-shadow: 0 5px 20px rgba(38,50,56,.08);
}

.mission-card {
    border: 1px solid rgba(38,50,56,.08);
    border-radius: 16px;
    background: white;
    transition: .15s ease;
}

.mission-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(38,50,56,.10);
}

.mission-icon {
    width: 50px;
    min-width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.icon-collecte {
    background: #e3f2fd;
    color: #2B7A9B;
}

.icon-service {
    background: #fff8e1;
    color: #b8860b;
}

.meta-line {
    color: #66756c;
    font-size: 14px;
    margin-bottom: 6px;
}

.empty-box {
    border: 1px dashed #cfd8dc;
    border-radius: 16px;
    background: #fafcfa;
}

.annulation-zone {
    border-top: 1px solid #edf0ed;
    margin-top: 14px;
    padding-top: 14px;
}

.past-event {
    font-size: 13px;
    color: #909090;
}
</style>

<nav class="navbar navbar-expand-lg benevole-navbar shadow-sm">

<div class="container-fluid px-lg-4">

<a
    class="navbar-brand"
    href="/benevole/dashboard.php"
>
    <i class="fa-solid fa-leaf me-2"></i>
    No More Waste
</a>

<button
    class="navbar-toggler bg-light"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#navbarBenevole"
>
    <span class="navbar-toggler-icon"></span>
</button>

<div
    class="collapse navbar-collapse"
    id="navbarBenevole"
>

<ul class="navbar-nav me-auto">

<li class="nav-item">
<a
    class="nav-link"
    href="/benevole/dashboard.php"
>
    <i class="fa-solid fa-house me-1"></i>
    Tableau de bord
</a>
</li>

<li class="nav-item">
<a
    class="nav-link"
    href="/benevole/offres.php"
>
    <i class="fa-solid fa-hand-holding-heart me-1"></i>
    Offres disponibles
</a>
</li>

<li class="nav-item">
<a
    class="nav-link active"
    href="/benevole/planning.php"
>
    <i class="fa-solid fa-calendar-days me-1"></i>
    Mon planning
</a>
</li>

<li class="nav-item">
<a
    class="nav-link"
    href="/benevole/profil.php"
>
    <i class="fa-solid fa-user me-1"></i>
    Mon profil
</a>
</li>

<li class="nav-item">
<a
    class="nav-link"
    href="/public/accueil.php"
>
    <i class="fa-solid fa-globe me-1"></i>
    Site public
</a>
</li>

</ul>

<a
    href="/logout.php"
    class="btn btn-outline-light btn-sm"
>
    <i class="fa-solid fa-right-from-bracket me-1"></i>
    Déconnexion
</a>

</div>
</div>
</nav>

<main class="container planning-container py-5">

<div class="mb-4">

<h1 class="fw-bold mb-2">
    Mon planning
</h1>

<p class="text-muted mb-0">
    Retrouvez les collectes et les services auxquels vous êtes affecté.
</p>

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

<div class="card planning-card mb-5">

<div class="card-body p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="mb-1">
    Mes collectes
</h3>

<p class="text-muted mb-0">
    Missions de collecte qui vous ont été attribuées.
</p>

</div>

<span class="badge bg-primary fs-6">
    <?= count($collectes) ?>
</span>

</div>

<?php if (empty($collectes)): ?>

<div class="empty-box text-center p-5">

<i
    class="fa-solid fa-truck fa-3x mb-3"
    style="color:#7BC96F;"
></i>

<h5>
    Aucune collecte planifiée
</h5>

<p class="text-muted mb-0">
    Vous n'êtes actuellement affecté à aucune collecte.
</p>

</div>

<?php else: ?>

<div class="row g-3">

<?php foreach ($collectes as $collecte): ?>

<?php
$idCollecte = (int) ($collecte["id_collecte"] ?? 0);

$dateCollecte = substr(
    (string) ($collecte["date_collecte"] ?? ""),
    0,
    10
);

$peutAnnulerCollecte =
    $dateCollecte !== "" &&
    $dateCollecte >= date("Y-m-d");
?>

<div class="col-lg-6">

<div class="mission-card p-4 h-100">

<div class="d-flex gap-3">

<div class="mission-icon icon-collecte">
    <i class="fa-solid fa-truck"></i>
</div>

<div class="flex-grow-1">

<div class="d-flex justify-content-between gap-2">

<h5 class="mb-2">
    Collecte #<?= $idCollecte ?>
</h5>

<?php if (!empty($collecte["statut"])): ?>

<span class="badge bg-success align-self-start">
    <?= htmlspecialchars($collecte["statut"]) ?>
</span>

<?php endif; ?>

</div>

<?php if (!empty($collecte["date_collecte"])): ?>

<div class="meta-line">
    <i class="fa-regular fa-calendar me-2"></i>
    <?= htmlspecialchars($collecte["date_collecte"]) ?>
</div>

<?php endif; ?>

<?php if (!empty($collecte["heure_debut"])): ?>

<div class="meta-line">

<i class="fa-regular fa-clock me-2"></i>

<?= htmlspecialchars($collecte["heure_debut"]) ?>

<?php if (!empty($collecte["heure_fin"])): ?>
    -
    <?= htmlspecialchars($collecte["heure_fin"]) ?>
<?php endif; ?>

</div>

<?php endif; ?>

<?php if (!empty($collecte["adresse"])): ?>

<div class="meta-line">

<i class="fa-solid fa-location-dot me-2"></i>

<?= htmlspecialchars($collecte["adresse"]) ?>

<?= !empty($collecte["code_postal"])
    ? htmlspecialchars(" " . $collecte["code_postal"])
    : ""
?>

<?= !empty($collecte["ville"])
    ? htmlspecialchars(" " . $collecte["ville"])
    : ""
?>

</div>

<?php endif; ?>

<?php if (!empty($collecte["role_collecte"])): ?>

<div class="meta-line">

<i class="fa-solid fa-user-tag me-2"></i>

Rôle :

<strong>
    <?= htmlspecialchars($collecte["role_collecte"]) ?>
</strong>

</div>

<?php endif; ?>

<div class="annulation-zone">

<?php if ($peutAnnulerCollecte): ?>

<form
    method="post"
    onsubmit="return confirm('Êtes-vous sûr de vouloir annuler votre participation à cette collecte ?');"
>

<input
    type="hidden"
    name="action"
    value="annuler_collecte"
>

<input
    type="hidden"
    name="id_collecte"
    value="<?= $idCollecte ?>"
>

<button
    type="submit"
    class="btn btn-outline-danger btn-sm"
>
    <i class="fa-solid fa-xmark me-1"></i>
    Annuler ma participation
</button>

</form>

<?php else: ?>

<div class="past-event">
    <i class="fa-solid fa-lock me-1"></i>
    Cette collecte est passée, l'annulation n'est plus possible.
</div>

<?php endif; ?>

</div>

</div>
</div>
</div>
</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
</div>

<div class="card planning-card">

<div class="card-body p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h3 class="mb-1">
    Mes services
</h3>

<p class="text-muted mb-0">
    Services et ateliers auxquels vous participez.
</p>

</div>

<span class="badge bg-warning text-dark fs-6">
    <?= count($services) ?>
</span>

</div>

<?php if (empty($services)): ?>

<div class="empty-box text-center p-5">

<i
    class="fa-solid fa-calendar-check fa-3x mb-3"
    style="color:#7BC96F;"
></i>

<h5>
    Aucun service planifié
</h5>

<p class="text-muted mb-0">
    Vous n'êtes actuellement affecté à aucun service.
</p>

</div>

<?php else: ?>

<div class="row g-3">

<?php foreach ($services as $service): ?>

<?php
$idService = (int) ($service["id_service"] ?? 0);

$dateService = substr(
    (string) ($service["date_service"] ?? ""),
    0,
    10
);

$peutAnnulerService =
    $dateService !== "" &&
    $dateService >= date("Y-m-d");
?>

<div class="col-lg-6">

<div class="mission-card p-4 h-100">

<div class="d-flex gap-3">

<div class="mission-icon icon-service">
    <i class="fa-solid fa-calendar-check"></i>
</div>

<div class="flex-grow-1">

<div class="d-flex justify-content-between gap-2">

<h5 class="mb-2">
    <?= htmlspecialchars(
        $service["nom"] ?? "Service"
    ) ?>
</h5>

<?php if (!empty($service["role_service"])): ?>

<span class="badge bg-success align-self-start">
    <?= htmlspecialchars($service["role_service"]) ?>
</span>

<?php endif; ?>

</div>

<?php if (!empty($service["date_service"])): ?>

<div class="meta-line">
    <i class="fa-regular fa-calendar me-2"></i>
    <?= htmlspecialchars($service["date_service"]) ?>
</div>

<?php endif; ?>

<?php if (!empty($service["heure_debut"])): ?>

<div class="meta-line">

<i class="fa-regular fa-clock me-2"></i>

<?= htmlspecialchars($service["heure_debut"]) ?>

<?php if (!empty($service["heure_fin"])): ?>
    -
    <?= htmlspecialchars($service["heure_fin"]) ?>
<?php endif; ?>

</div>

<?php endif; ?>

<?php if (!empty($service["lieu"])): ?>

<div class="meta-line">

<i class="fa-solid fa-location-dot me-2"></i>

<?= htmlspecialchars($service["lieu"]) ?>

</div>

<?php endif; ?>

<?php if (!empty($service["statut_service"])): ?>

<div class="meta-line">

<i class="fa-solid fa-circle-info me-2"></i>

Statut :
<?= htmlspecialchars($service["statut_service"]) ?>

</div>

<?php endif; ?>

<div class="annulation-zone">

<?php if ($peutAnnulerService): ?>

<form
    method="post"
    onsubmit="return confirm('Êtes-vous sûr de vouloir annuler votre participation à ce service ?');"
>

<input
    type="hidden"
    name="action"
    value="annuler_service"
>

<input
    type="hidden"
    name="id_service"
    value="<?= $idService ?>"
>

<button
    type="submit"
    class="btn btn-outline-danger btn-sm"
>
    <i class="fa-solid fa-xmark me-1"></i>
    Annuler ma participation
</button>

</form>

<?php else: ?>

<div class="past-event">
    <i class="fa-solid fa-lock me-1"></i>
    Ce service est passé, l'annulation n'est plus possible.
</div>

<?php endif; ?>

</div>

</div>
</div>
</div>
</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
</div>

</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>