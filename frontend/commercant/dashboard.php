<?php
// COMMERCANT — Tableau de bord — Compte + adhésion + collectes + services

session_start();

require_once __DIR__ . "/../includes/auth.php";
exigerRole("COMMERCANT");

require_once __DIR__ . "/../includes/api.php";

$espace = API::get("/api/commercant/espace");

if (!is_array($espace) || isset($espace["error"])) {
    $espace = [];
}

$adhesion = $espace["adhesion_active"] ?? null;
$collectes = is_array($espace["collectes"] ?? null) ? $espace["collectes"] : [];
$services = is_array($espace["services"] ?? null) ? $espace["services"] : [];

$formatDate = static function (?string $date): string {
    if (!$date) {
        return "—";
    }

    $timestamp = strtotime($date);
    return $timestamp ? date("d/m/Y", $timestamp) : $date;
};

$formatStatut = static function (?string $statut): string {
    if (!$statut) {
        return "—";
    }

    return ucfirst(strtolower(str_replace("_", " ", $statut)));
};

$classeStatutCollecte = static function (?string $statut): string {
    return match ($statut) {
        "TERMINEE" => "success",
        "EN_COURS" => "primary",
        "PLANIFIEE", "VALIDEE" => "info",
        "ANNULEE" => "danger",
        default => "secondary",
    };
};

$classeStatutInscription = static function (?string $statut): string {
    return match ($statut) {
        "CONFIRME" => "success",
        "INSCRIT" => "primary",
        "ANNULE" => "danger",
        default => "secondary",
    };
};

$joursRestants = null;
if ($adhesion && !empty($adhesion["date_fin"])) {
    try {
        $aujourdhui = new DateTime("today");
        $finAdhesion = new DateTime($adhesion["date_fin"]);
        $joursRestants = (int) $aujourdhui->diff($finAdhesion)->format("%r%a");
    } catch (Throwable $e) {
        $joursRestants = null;
    }
}

$raisonSociale = trim((string) ($espace["raison_sociale"] ?? ""));
$nomComplet = trim((string) ($espace["prenom"] ?? "") . " " . (string) ($espace["nom"] ?? ""));
$adresseComplete = trim(
    (string) ($espace["adresse"] ?? "") .
    (!empty($espace["code_postal"]) || !empty($espace["ville"])
        ? ", " . trim((string) ($espace["code_postal"] ?? "") . " " . (string) ($espace["ville"] ?? ""))
        : "")
, " ,");

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navbar.php";
?>



<style>
    body {
        background: #f4f8f4;
    }

    .merchant-page {
        max-width: 1220px;
        margin: 0 auto;
        padding: 32px 20px 48px;
    }

    .merchant-hero {
        background: linear-gradient(135deg, #3f7d50 0%, #5c9668 100%);
        color: #fff;
        border-radius: 22px;
        padding: 28px 30px;
        box-shadow: 0 10px 28px rgba(63, 125, 80, .16);
        margin-bottom: 24px;
    }

    .merchant-hero .eyebrow {
        text-transform: uppercase;
        letter-spacing: .08em;
        font-size: .72rem;
        font-weight: 700;
        opacity: .8;
        margin-bottom: 6px;
    }

    .merchant-hero h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
    }

    .merchant-hero .subline {
        margin-top: 8px;
        opacity: .9;
        font-size: .95rem;
    }

    .hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .hero-badge {
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 999px;
        padding: 8px 12px;
        font-size: .86rem;
        font-weight: 600;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 1.35fr .95fr;
        gap: 22px;
        margin-bottom: 24px;
    }

    .dashboard-card {
        background: #fff;
        border: 1px solid #e6ece6;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .045);
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0 0 18px;
        color: #26352a;
    }

    .card-title i {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #edf6ef;
        color: #3f7d50;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 28px;
    }

    .info-item {
        min-width: 0;
    }

    .info-label {
        color: #7a857d;
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .info-value {
        color: #202a22;
        font-size: .96rem;
        font-weight: 600;
        word-break: break-word;
    }

    .info-item.full {
        grid-column: 1 / -1;
    }

    .adhesion-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #f3f8f4;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 16px;
    }

    .adhesion-status strong {
        color: #2f6f46;
    }

    .adhesion-list {
        display: grid;
        gap: 10px;
        margin-bottom: 18px;
    }

    .adhesion-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        padding-bottom: 10px;
        border-bottom: 1px solid #edf0ed;
    }

    .adhesion-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .adhesion-row span {
        color: #7a857d;
        font-size: .86rem;
    }

    .adhesion-row strong {
        text-align: right;
        font-size: .92rem;
    }

    .action-stack {
        display: grid;
        gap: 10px;
    }

    .action-stack .btn {
        border-radius: 11px;
        padding: 10px 14px;
        font-weight: 600;
    }

    .activity-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
    }

    .section-header h2 {
        font-size: 1.08rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 9px;
        color: #26352a;
    }

    .activity-list {
        display: grid;
        gap: 12px;
    }

    .activity-item {
        border: 1px solid #e9eee9;
        border-radius: 14px;
        padding: 14px 16px;
        background: #fbfcfb;
    }

    .activity-item-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .activity-title {
        font-weight: 700;
        color: #222b24;
        margin-bottom: 5px;
    }

    .activity-meta {
        color: #737d76;
        font-size: .84rem;
        line-height: 1.55;
    }

    .activity-meta i {
        width: 16px;
        text-align: center;
        margin-right: 4px;
    }

    .empty-state {
        border: 1px dashed #d8e1d9;
        background: #f8fbf8;
        border-radius: 14px;
        padding: 28px 18px;
        text-align: center;
        color: #778079;
    }

    .empty-state i {
        font-size: 1.4rem;
        margin-bottom: 8px;
        color: #79a282;
    }

    .empty-state strong {
        display: block;
        color: #47524a;
        margin-bottom: 3px;
    }

    @media (max-width: 991.98px) {
        .dashboard-grid,
        .activity-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .merchant-page {
            padding: 20px 14px 34px;
        }

        .merchant-hero {
            padding: 22px;
        }

        .merchant-hero h1 {
            font-size: 1.55rem;
        }

        .profile-grid {
            grid-template-columns: 1fr;
        }

        .info-item.full {
            grid-column: auto;
        }

        .activity-item-top,
        .section-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="merchant-page">

    <section class="merchant-hero">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="eyebrow">Espace adhérent</div>
                <h1><?= htmlspecialchars($raisonSociale !== "" ? $raisonSociale : "Mon entreprise") ?></h1>

                <div class="subline">
                    <?php if ($nomComplet !== ""): ?>
                        <?= htmlspecialchars($nomComplet) ?>
                    <?php endif; ?>

                    <?php if (!empty($espace["email"])): ?>
                        <?= $nomComplet !== "" ? " · " : "" ?>
                        <?= htmlspecialchars($espace["email"]) ?>
                    <?php endif; ?>
                </div>

                <div class="hero-badges">
                    <?php if ($adhesion): ?>
                        <span class="hero-badge">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            Adhésion active
                        </span>
                    <?php else: ?>
                        <span class="hero-badge">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            Adhésion inactive
                        </span>
                    <?php endif; ?>

                    <span class="hero-badge">
                        <i class="fa-solid fa-calendar-check me-1"></i>
                        <?= count($services) ?> inscription<?= count($services) > 1 ? "s" : "" ?>
                    </span>

                    <span class="hero-badge">
                        <i class="fa-solid fa-truck me-1"></i>
                        <?= count($collectes) ?> collecte<?= count($collectes) > 1 ? "s" : "" ?>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <div class="dashboard-grid">

        <section class="dashboard-card">
            <h2 class="card-title">
                <i class="fa-solid fa-building"></i>
                Mes informations
            </h2>

            <div class="profile-grid">
                <div class="info-item">
                    <div class="info-label">Raison sociale</div>
                    <div class="info-value"><?= htmlspecialchars($raisonSociale !== "" ? $raisonSociale : "—") ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">SIRET</div>
                    <div class="info-value"><?= htmlspecialchars($espace["siret"] ?? "—") ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Secteur</div>
                    <div class="info-value"><?= htmlspecialchars($espace["secteur_activite"] ?? "—") ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Contact</div>
                    <div class="info-value"><?= htmlspecialchars($nomComplet !== "" ? $nomComplet : "—") ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Téléphone</div>
                    <div class="info-value"><?= htmlspecialchars($espace["telephone"] ?? "—") ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($espace["email"] ?? "—") ?></div>
                </div>

                <div class="info-item full">
                    <div class="info-label">Adresse</div>
                    <div class="info-value"><?= htmlspecialchars($adresseComplete !== "" ? $adresseComplete : "—") ?></div>
                </div>
            </div>
        </section>

        <section class="dashboard-card">
            <h2 class="card-title">
                <i class="fa-solid fa-id-card"></i>
                Mon adhésion
            </h2>

            <?php if ($adhesion): ?>
                <div class="adhesion-status">
                    <div>
                        <div class="small text-muted">Statut actuel</div>
                        <strong>Adhérent actif</strong>
                    </div>
                    <i class="fa-solid fa-circle-check text-success fs-5"></i>
                </div>

                <div class="adhesion-list">
                    <div class="adhesion-row">
                        <span>Début</span>
                        <strong><?= htmlspecialchars($formatDate($adhesion["date_debut"] ?? null)) ?></strong>
                    </div>

                    <div class="adhesion-row">
                        <span>Fin</span>
                        <strong><?= htmlspecialchars($formatDate($adhesion["date_fin"] ?? null)) ?></strong>
                    </div>

                    <div class="adhesion-row">
                        <span>Cotisation</span>
                        <strong><?= htmlspecialchars(number_format((float) ($adhesion["montant"] ?? 0), 2, ",", " ")) ?> €</strong>
                    </div>

                    <?php if ($joursRestants !== null): ?>
                        <div class="adhesion-row">
                            <span>Temps restant</span>
                            <strong>
                                <?= $joursRestants >= 0
                                    ? $joursRestants . " jour" . ($joursRestants > 1 ? "s" : "")
                                    : "Expirée" ?>
                            </strong>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    Aucune adhésion active. L'accès aux services est indisponible.
                </div>
            <?php endif; ?>

            <div class="action-stack">
                <a href="/public/services.php"
                   class="btn btn-success <?= $adhesion ? "" : "disabled" ?>">
                    <i class="fa-solid fa-calendar-days me-1"></i>
                    Voir les services
                </a>

                <a href="/public/demande-collecte.php"
                   class="btn btn-outline-success">
                    <i class="fa-solid fa-truck me-1"></i>
                    Demander une collecte
                </a>
            </div>
        </section>

    </div>

    <div class="activity-grid">

        <section class="dashboard-card">
            <div class="section-header">
                <h2>
                    <i class="fa-solid fa-calendar-check text-success"></i>
                    Mes services
                </h2>
            </div>

            <?php if (empty($services)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-calendar-xmark"></i>
                    <strong>Aucune inscription</strong>
                    <span>Vos prochains services apparaîtront ici.</span>
                </div>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($services as $service): ?>
                        <div class="activity-item">
                            <div class="activity-item-top">
                                <div>
                                    <div class="activity-title">
                                        <?= htmlspecialchars($service["nom"] ?? "Service") ?>
                                    </div>

                                    <div class="activity-meta">
                                        <div>
                                            <i class="fa-regular fa-calendar"></i>
                                            <?= htmlspecialchars($formatDate($service["date_service"] ?? null)) ?>

                                            <?php if (!empty($service["heure_debut"])): ?>
                                                · <?= htmlspecialchars($service["heure_debut"]) ?>
                                                <?php if (!empty($service["heure_fin"])): ?>
                                                    – <?= htmlspecialchars($service["heure_fin"]) ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($service["lieu"])): ?>
                                            <div>
                                                <i class="fa-solid fa-location-dot"></i>
                                                <?= htmlspecialchars($service["lieu"]) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <span class="badge bg-<?= $classeStatutInscription($service["statut_inscription"] ?? null) ?>">
                                    <?= htmlspecialchars($formatStatut($service["statut_inscription"] ?? null)) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-card">
            <div class="section-header">
                <h2>
                    <i class="fa-solid fa-truck text-success"></i>
                    Mes collectes
                </h2>
            </div>

            <?php if (empty($collectes)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <strong>Aucune demande</strong>
                    <span>Vos demandes de collecte apparaîtront ici.</span>
                </div>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($collectes as $collecte): ?>
                        <div class="activity-item">
                            <div class="activity-item-top">
                                <div>
                                    <div class="activity-title">
                                        <?= htmlspecialchars($formatDate($collecte["date_collecte"] ?? null)) ?>
                                        <?php if (!empty($collecte["ville"])): ?>
                                            · <?= htmlspecialchars($collecte["ville"]) ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="activity-meta">
                                        <?php if (!empty($collecte["heure_debut"])): ?>
                                            <div>
                                                <i class="fa-regular fa-clock"></i>
                                                <?= htmlspecialchars($collecte["heure_debut"]) ?>
                                                <?php if (!empty($collecte["heure_fin"])): ?>
                                                    – <?= htmlspecialchars($collecte["heure_fin"]) ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($collecte["adresse"])): ?>
                                            <div>
                                                <i class="fa-solid fa-location-dot"></i>
                                                <?= htmlspecialchars($collecte["adresse"]) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <span class="badge bg-<?= $classeStatutCollecte($collecte["statut"] ?? null) ?>">
                                    <?= htmlspecialchars($formatStatut($collecte["statut"] ?? null)) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>

</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
