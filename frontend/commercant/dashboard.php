<?php
session_start();

require_once __DIR__ . "/../includes/auth.php";
exigerRole("COMMERCANT");

require_once __DIR__ . "/../includes/api.php";

$espace = API::get("/api/commercant/espace");

if (!is_array($espace) || isset($espace["error"])) {
    $espace = [];
}

$adhesion = $espace["adhesion_active"] ?? null;
$collectes = $espace["collectes"] ?? [];
$services = $espace["services"] ?? [];

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navbar.php";
?>

<div class="container py-5">

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="text-success fw-semibold mb-1">Espace commerçant</p>
            <h1 class="mb-1"><?= htmlspecialchars($espace["raison_sociale"] ?? "Mon entreprise") ?></h1>
            <p class="text-muted mb-0">
                <?= htmlspecialchars(($espace["prenom"] ?? "") . " " . ($espace["nom"] ?? "")) ?>
                · <?= htmlspecialchars($espace["email"] ?? "") ?>
            </p>
        </div>

        <?php if ($adhesion): ?>
            <span class="badge bg-success fs-6 px-3 py-2">Adhérent actif</span>
        <?php else: ?>
            <span class="badge bg-warning text-dark fs-6 px-3 py-2">Aucune adhésion active</span>
        <?php endif; ?>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5><i class="fa-solid fa-id-card text-success me-2"></i>Mon adhésion</h5>
                    <?php if ($adhesion): ?>
                        <p class="mb-1"><strong>Du :</strong> <?= htmlspecialchars($adhesion["date_debut"]) ?></p>
                        <p class="mb-1"><strong>Au :</strong> <?= htmlspecialchars($adhesion["date_fin"]) ?></p>
                        <p class="mb-0"><strong>Montant :</strong> <?= htmlspecialchars($adhesion["montant"]) ?> €</p>
                    <?php else: ?>
                        <p class="text-muted mb-0">
                            Votre compte commerçant est actif, mais aucune cotisation annuelle
                            active n'est enregistrée. Les services sont donc indisponibles.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5><i class="fa-solid fa-calendar-days text-success me-2"></i>Services</h5>
                    <p class="text-muted">
                        Les services No More Waste sont réservés aux commerçants adhérents.
                    </p>
                    <a href="/public/services.php"
                       class="btn btn-success mt-auto <?= $adhesion ? "" : "disabled" ?>">
                        Voir les services
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5><i class="fa-solid fa-truck text-primary me-2"></i>Collectes</h5>
                    <p class="text-muted">
                        Une collecte peut être demandée avec ou sans adhésion.
                    </p>
                    <a href="/public/demande-collecte.php" class="btn btn-outline-primary mt-auto">
                        Demander une collecte
                    </a>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3">Mes inscriptions aux services</h4>

                    <?php if (empty($services)): ?>
                        <p class="text-muted mb-0">Aucune inscription pour le moment.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($services as $service): ?>
                                <div class="list-group-item px-0">
                                    <strong><?= htmlspecialchars($service["nom"] ?? "") ?></strong>
                                    <div class="small text-muted mt-1">
                                        <?= htmlspecialchars($service["date_service"] ?? "") ?>
                                        <?= htmlspecialchars($service["heure_debut"] ?? "") ?>
                                        <?php if (!empty($service["lieu"])): ?>
                                            · <?= htmlspecialchars($service["lieu"]) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3">Mes demandes de collecte</h4>

                    <?php if (empty($collectes)): ?>
                        <p class="text-muted mb-0">Aucune demande pour le moment.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($collectes as $collecte): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between gap-2">
                                        <strong>
                                            <?= htmlspecialchars($collecte["date_collecte"] ?? "") ?>
                                            · <?= htmlspecialchars($collecte["ville"] ?? "") ?>
                                        </strong>
                                        <span class="badge bg-light text-dark">
                                            <?= htmlspecialchars($collecte["statut"] ?? "") ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <?= htmlspecialchars($collecte["heure_debut"] ?? "") ?>
                                        –
                                        <?= htmlspecialchars($collecte["heure_fin"] ?? "") ?>
                                        · <?= htmlspecialchars($collecte["adresse"] ?? "") ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
