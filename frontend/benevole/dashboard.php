<?php
session_start();

require_once __DIR__ . "/../includes/auth.php";
exigerRole("BENEVOLE");

require_once __DIR__ . "/../includes/api.php";

$utilisateur = $_SESSION["utilisateur"] ?? [];

$prenom = $utilisateur["prenom"] ?? "";
$nom = $utilisateur["nom"] ?? "";

$planning = API::get("/api/benevole/planning");

if (!is_array($planning)) {
    $planning = [];
}

$offres = API::get("/api/offres-benevoles");

if (!is_array($offres)) {
    $offres = [];
}

$offresOuvertes = [];

foreach ($offres as $offre) {
    if (($offre["statut"] ?? "") === "OUVERTE") {
        $offresOuvertes[] = $offre;
    }
}

$collectes = $planning["collectes"] ?? [];
$services = $planning["services"] ?? [];

if (!is_array($collectes)) {
    $collectes = [];
}

if (!is_array($services)) {
    $services = [];
}

$nbOffres = count($offresOuvertes);
$nbCollectes = count($collectes);
$nbServices = count($services);

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
    color: rgba(255, 255, 255, 0.9) !important;
    border-radius: 10px;
    padding: 8px 14px !important;
}

.benevole-navbar .nav-link:hover {
    background: rgba(255, 255, 255, 0.12);
    color: white !important;
}

.benevole-navbar .nav-link.active {
    background: rgba(255, 255, 255, 0.18);
    color: white !important;
}

.benevole-user {
    color: white;
    font-size: 14px;
}

.dashboard-container {
    max-width: 1250px;
}

.welcome-card {
    border: 0;
    border-radius: 20px;
    background: white;
    box-shadow: 0 5px 20px rgba(38, 50, 56, 0.08);
}

.dashboard-card {
    border: 0;
    border-radius: 18px;
    background: white;
    box-shadow: 0 5px 20px rgba(38, 50, 56, 0.08);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(38, 50, 56, 0.12);
}

.dashboard-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.icon-offres {
    background: #e8f5e9;
    color: #2E7D32;
}

.icon-collectes {
    background: #e3f2fd;
    color: #2B7A9B;
}

.icon-services {
    background: #fff8e1;
    color: #b8860b;
}

.dashboard-number {
    font-size: 34px;
    font-weight: 700;
    line-height: 1;
}

.section-title {
    font-weight: 700;
    color: #263238;
}

.quick-link {
    text-decoration: none;
    color: inherit;
}

.quick-link:hover {
    color: inherit;
}

.empty-planning {
    border: 1px dashed #cfd8dc;
    border-radius: 14px;
    background: #fafcfa;
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

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a
                        class="nav-link active"
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

                        <?php if ($nbOffres > 0): ?>
                            <span class="badge bg-warning text-dark ms-1">
                                <?= $nbOffres ?>
                            </span>
                        <?php endif; ?>
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

            <div class="d-flex align-items-center gap-3">

                <div class="benevole-user d-none d-lg-block">

                    <i class="fa-solid fa-circle-user me-1"></i>

                    <?= htmlspecialchars(
                        trim($prenom . " " . $nom)
                    ) ?>

                </div>

                <a
                    href="/logout.php"
                    class="btn btn-outline-light btn-sm"
                >
                    <i class="fa-solid fa-right-from-bracket me-1"></i>
                    Déconnexion
                </a>

            </div>

        </div>

    </div>

</nav>

<main class="container dashboard-container py-5">

    <div class="welcome-card p-4 p-md-5 mb-4">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <span class="badge bg-success mb-3">
                    Espace bénévole
                </span>

                <h1 class="fw-bold mb-2">

                    Bonjour
                    <?= htmlspecialchars($prenom) ?>
                    👋

                </h1>

                <p class="text-muted mb-0">

                    Retrouvez les missions qui recherchent des bénévoles,
                    indiquez vos disponibilités et consultez votre planning.

                </p>

            </div>

            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">

                <a
                    href="/benevole/offres.php"
                    class="btn btn-nmw btn-lg"
                >
                    <i class="fa-solid fa-hand-holding-heart me-2"></i>
                    Voir les offres
                </a>

            </div>

        </div>

    </div>

    <div class="row g-4 mb-5">

        <div class="col-md-4">

            <a
                href="/benevole/offres.php"
                class="quick-link"
            >

                <div class="card dashboard-card h-100">

                    <div class="card-body p-4">

                        <div
                            class="d-flex justify-content-between
                                   align-items-start mb-4"
                        >

                            <div class="dashboard-icon icon-offres">
                                <i class="fa-solid fa-hand-holding-heart"></i>
                            </div>

                            <?php if ($nbOffres > 0): ?>

                                <span class="badge bg-success">
                                    Disponible
                                </span>

                            <?php endif; ?>

                        </div>

                        <div class="dashboard-number">
                            <?= $nbOffres ?>
                        </div>

                        <h5 class="mt-2 mb-1">
                            Offres disponibles
                        </h5>

                        <p class="text-muted mb-0">
                            Missions auxquelles vous pouvez proposer votre aide.
                        </p>

                    </div>

                </div>

            </a>

        </div>

        <div class="col-md-4">

            <a
                href="/benevole/planning.php"
                class="quick-link"
            >

                <div class="card dashboard-card h-100">

                    <div class="card-body p-4">

                        <div class="dashboard-icon icon-collectes mb-4">
                            <i class="fa-solid fa-truck"></i>
                        </div>

                        <div class="dashboard-number">
                            <?= $nbCollectes ?>
                        </div>

                        <h5 class="mt-2 mb-1">
                            Mes collectes
                        </h5>

                        <p class="text-muted mb-0">
                            Collectes auxquelles vous êtes affecté.
                        </p>

                    </div>

                </div>

            </a>

        </div>

        <div class="col-md-4">

            <a
                href="/benevole/planning.php"
                class="quick-link"
            >

                <div class="card dashboard-card h-100">

                    <div class="card-body p-4">

                        <div class="dashboard-icon icon-services mb-4">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>

                        <div class="dashboard-number">
                            <?= $nbServices ?>
                        </div>

                        <h5 class="mt-2 mb-1">
                            Mes services
                        </h5>

                        <p class="text-muted mb-0">
                            Services auxquels vous participez.
                        </p>

                    </div>

                </div>

            </a>

        </div>

    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h4 class="section-title mb-1">
                Mes prochaines activités
            </h4>

            <p class="text-muted mb-0">
                Retrouvez rapidement vos prochaines missions.
            </p>

        </div>

        <a
            href="/benevole/planning.php"
            class="btn btn-outline-success"
        >
            Voir tout le planning
        </a>

    </div>

    <?php if (
        empty($collectes) &&
        empty($services)
    ): ?>

        <div class="empty-planning text-center p-5">

            <i
                class="fa-regular fa-calendar fa-3x mb-3"
                style="color:#7BC96F;"
            ></i>

            <h5>
                Aucune mission planifiée
            </h5>

            <p class="text-muted">
                Vous n'êtes actuellement affecté à aucune mission.
                Consultez les offres disponibles pour proposer votre aide.
            </p>

            <a
                href="/benevole/offres.php"
                class="btn btn-nmw"
            >
                Consulter les offres
            </a>

        </div>

    <?php else: ?>

        <div class="row g-3">

            <?php foreach (
                array_slice($collectes, 0, 3) as $collecte
            ): ?>

                <div class="col-lg-4">

                    <div class="card dashboard-card h-100">

                        <div class="card-body">

                            <span class="badge bg-primary mb-2">
                                Collecte
                            </span>

                            <h5>
                                Collecte
                                #<?= (int) (
                                    $collecte["id_collecte"] ?? 0
                                ) ?>
                            </h5>

                            <?php if (
                                !empty($collecte["date_collecte"])
                            ): ?>

                                <p class="text-muted mb-1">

                                    <i class="fa-solid fa-calendar me-1"></i>

                                    <?= htmlspecialchars(
                                        $collecte["date_collecte"]
                                    ) ?>

                                </p>

                            <?php endif; ?>

                            <?php if (
                                !empty($collecte["adresse"])
                            ): ?>

                                <p class="text-muted mb-0">

                                    <i class="fa-solid fa-location-dot me-1"></i>

                                    <?= htmlspecialchars(
                                        $collecte["adresse"]
                                    ) ?>

                                </p>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

            <?php foreach (
                array_slice($services, 0, 3) as $service
            ): ?>

                <div class="col-lg-4">

                    <div class="card dashboard-card h-100">

                        <div class="card-body">

                            <span class="badge bg-warning text-dark mb-2">
                                Service
                            </span>

                            <h5>
                                <?= htmlspecialchars(
                                    $service["nom"] ??
                                    "Service"
                                ) ?>
                            </h5>

                            <?php if (
                                !empty($service["date_service"])
                            ): ?>

                                <p class="text-muted mb-1">

                                    <i class="fa-solid fa-calendar me-1"></i>

                                    <?= htmlspecialchars(
                                        $service["date_service"]
                                    ) ?>

                                </p>

                            <?php endif; ?>

                            <?php if (
                                !empty($service["lieu"])
                            ): ?>

                                <p class="text-muted mb-0">

                                    <i class="fa-solid fa-location-dot me-1"></i>

                                    <?= htmlspecialchars(
                                        $service["lieu"]
                                    ) ?>

                                </p>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>