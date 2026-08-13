<?php
session_start();

require_once __DIR__ . "/../includes/auth.php";
exigerRole("BENEVOLE");

require_once __DIR__ . "/../includes/api.php";

$utilisateur = $_SESSION["utilisateur"] ?? [];
$idUtilisateur = (int) ($utilisateur["id_utilisateur"] ?? 0);

$profil = API::get("/api/utilisateurs/get?id=" . $idUtilisateur);

if (!is_array($profil)) {
    $profil = [];
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

.profil-container {
    max-width: 1000px;
}

.profil-card {
    border: 0;
    border-radius: 18px;
    box-shadow: 0 5px 20px rgba(38,50,56,.08);
}

.profil-avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: #e8f5e9;
    color: #2E7D32;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    margin: auto;
}

.profil-line {
    padding: 14px 0;
    border-bottom: 1px solid #edf0ed;
}

.profil-line:last-child {
    border-bottom: 0;
}

.profil-label {
    color: #6b7971;
    font-size: 13px;
    font-weight: 600;
}

.profil-value {
    font-weight: 600;
    color: #263238;
}

.badge-actif {
    background: #e8f5e9;
    color: #2E7D32;
    border-radius: 999px;
    padding: 6px 12px;
    font-weight: 700;
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
    class="nav-link"
    href="/benevole/planning.php"
>
    <i class="fa-solid fa-calendar-days me-1"></i>
    Mon planning
</a>
</li>

<li class="nav-item">
<a
    class="nav-link active"
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

<main class="container profil-container py-5">

<div class="mb-4">

<h1 class="fw-bold mb-2">
Mon profil
</h1>

<p class="text-muted">
Consultez les informations associées à votre compte bénévole.
</p>

</div>

<div class="row g-4">

<div class="col-lg-4">

<div class="card profil-card">

<div class="card-body text-center p-4">

<div class="profil-avatar mb-3">
<i class="fa-solid fa-user"></i>
</div>

<h4 class="mb-1">
<?= htmlspecialchars(
    trim(
        ($profil["prenom"] ?? "") .
        " " .
        ($profil["nom"] ?? "")
    )
) ?>
</h4>

<p class="text-muted mb-3">
Bénévole No More Waste
</p>

<?php if (!empty($profil["actif"])): ?>

<span class="badge-actif">
<i class="fa-solid fa-circle-check me-1"></i>
Compte actif
</span>

<?php else: ?>

<span class="badge bg-warning text-dark">
Compte en attente
</span>

<?php endif; ?>

</div>

</div>

</div>

<div class="col-lg-8">

<div class="card profil-card">

<div class="card-body p-4">

<h4 class="mb-4">
Mes informations
</h4>

<div class="profil-line">

<div class="profil-label">
Prénom
</div>

<div class="profil-value">
<?= htmlspecialchars(
    $profil["prenom"] ?? "Non renseigné"
) ?>
</div>

</div>

<div class="profil-line">

<div class="profil-label">
Nom
</div>

<div class="profil-value">
<?= htmlspecialchars(
    $profil["nom"] ?? "Non renseigné"
) ?>
</div>

</div>

<div class="profil-line">

<div class="profil-label">
Email
</div>

<div class="profil-value">
<?= htmlspecialchars(
    $profil["email"] ?? "Non renseigné"
) ?>
</div>

</div>

<div class="profil-line">

<div class="profil-label">
Téléphone
</div>

<div class="profil-value">
<?= htmlspecialchars(
    $profil["telephone"] ?? "Non renseigné"
) ?>
</div>

</div>

<div class="profil-line">

<div class="profil-label">
Ville
</div>

<div class="profil-value">
<?= htmlspecialchars(
    $profil["ville"] ?? "Non renseignée"
) ?>
</div>

</div>

<div class="profil-line">

<div class="profil-label">
Rôle
</div>

<div class="profil-value">
BENEVOLE
</div>

</div>

</div>

</div>

</div>

</div>

</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>