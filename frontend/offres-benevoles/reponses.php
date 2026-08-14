<?php
session_start();

require_once __DIR__ . "/../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once __DIR__ . "/../includes/api.php";

$idOffre = (int) ($_GET["id"] ?? 0);
$messageSucces = $_GET["success"] ?? "";
$messageErreur = $_GET["error"] ?? "";

$offres = API::get("/api/offres-benevoles");

if (!is_array($offres)) {
    $offres = [];
}

$offreSelectionnee = null;

foreach ($offres as $offre) {
    if ((int) ($offre["id_offre"] ?? 0) === $idOffre) {
        $offreSelectionnee = $offre;
        break;
    }
}

if (!$offreSelectionnee && $messageErreur === "") {
    $messageErreur = "Offre introuvable.";
}

$typeEvenement = $offreSelectionnee["type_evenement"] ?? "";
$idEvenement = (int) ($offreSelectionnee["id_evenement"] ?? 0);

$nombreBenevolesRequis = (int) ($offreSelectionnee["nombre_benevoles_requis"] ?? 0);
$nombreAffectes = (int) ($offreSelectionnee["nombre_affectes"] ?? 0);
$offreComplete = $nombreBenevolesRequis > 0 && $nombreAffectes >= $nombreBenevolesRequis;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $offreSelectionnee) {
    $idUtilisateur = (int) ($_POST["id_utilisateur"] ?? 0);

    if ($idUtilisateur <= 0) {
        header(
            "Location: reponses.php?id=" .
            $idOffre .
            "&error=" .
            urlencode("Bénévole invalide.")
        );
        exit;
    }

    if ($offreComplete) {
        header(
            "Location: reponses.php?id=" .
            $idOffre .
            "&error=" .
            urlencode("Cette offre est complète. Aucun bénévole supplémentaire ne peut être affecté.")
        );
        exit;
    }

    if ($typeEvenement === "COLLECTE") {
        $roleCollecte = $_POST["role_collecte"] ?? "MANUTENTION";

        $rolesAutorises = [
            "CHAUFFEUR",
            "MANUTENTION",
            "RESPONSABLE"
        ];

        if (!in_array($roleCollecte, $rolesAutorises, true)) {
            header(
                "Location: reponses.php?id=" .
                $idOffre .
                "&error=" .
                urlencode("Rôle de collecte invalide.")
            );
            exit;
        }

        $resultat = API::post("/api/collecte_benevoles/create", [
            "id_collecte" => $idEvenement,
            "id_utilisateur" => $idUtilisateur,
            "role_collecte" => $roleCollecte,
            "heure_arrivee" => $offreSelectionnee["heure_debut"] ?? "08:00",
            "heure_depart" => $offreSelectionnee["heure_fin"] ?? "18:00"
        ]);

        if (is_array($resultat) && isset($resultat["message"])) {
            header(
                "Location: reponses.php?id=" .
                $idOffre .
                "&success=" .
                urlencode("Bénévole affecté à la collecte avec succès.")
            );
            exit;
        }

        header(
            "Location: reponses.php?id=" .
            $idOffre .
            "&error=" .
            urlencode("Impossible d'affecter ce bénévole à la collecte.")
        );
        exit;
    }

    if ($typeEvenement === "SERVICE") {
        $resultat = API::post("/api/service-benevoles/create", [
            "id_service" => $idEvenement,
            "id_utilisateur" => $idUtilisateur,
            "role_service" => "BENEVOLE"
        ]);

        if (is_array($resultat) && isset($resultat["message"])) {
            header(
                "Location: reponses.php?id=" .
                $idOffre .
                "&success=" .
                urlencode("Bénévole affecté au service avec succès.")
            );
            exit;
        }

        header(
            "Location: reponses.php?id=" .
            $idOffre .
            "&error=" .
            urlencode("Impossible d'affecter ce bénévole au service.")
        );
        exit;
    }
}

$reponses = [];

if ($offreSelectionnee) {
    $reponses = API::get(
        "/api/offres-benevoles/reponses?id_offre=" . $idOffre
    );

    if (!is_array($reponses)) {
        $reponses = [];
    }
}

$affectations = [];

if ($typeEvenement === "COLLECTE") {
    $collecteBenevoles = API::get("/api/collecte_benevoles");

    if (!is_array($collecteBenevoles)) {
        $collecteBenevoles = [];
    }

    foreach ($collecteBenevoles as $affectation) {
        if (
            (int) ($affectation["id_collecte"] ?? 0) === $idEvenement
        ) {
            $affectations[
                (int) ($affectation["id_utilisateur"] ?? 0)
            ] = true;
        }
    }
}

if ($typeEvenement === "SERVICE") {
    $serviceBenevoles = API::get(
        "/api/service-benevoles?id_service=" . $idEvenement
    );

    if (!is_array($serviceBenevoles)) {
        $serviceBenevoles = [];
    }

    foreach ($serviceBenevoles as $affectation) {
        $affectations[
            (int) ($affectation["id_utilisateur"] ?? 0)
        ] = true;
    }
}

$nombreAffectes = count($affectations);
$offreComplete = $nombreBenevolesRequis > 0 && $nombreAffectes >= $nombreBenevolesRequis;

$reponsesParJour = [];

foreach ($reponses as $reponse) {
    $idJour = (int) ($reponse["id_jour"] ?? 0);

    if ($idJour <= 0) {
        continue;
    }

    if (!isset($reponsesParJour[$idJour])) {
        $reponsesParJour[$idJour] = [
            "date_jour" => $reponse["date_jour"] ?? "",
            "benevoles" => []
        ];
    }

    $reponsesParJour[$idJour]["benevoles"][] = [
        "id_utilisateur" => (int) ($reponse["id_utilisateur"] ?? 0),
        "nom" => $reponse["nom"] ?? "",
        "prenom" => $reponse["prenom"] ?? "",
        "email" => $reponse["email"] ?? ""
    ];
}

$nbReponses = count(array_unique(array_map(
    fn($r) => (int) ($r["id_utilisateur"] ?? 0),
    $reponses
)));

$retour = $typeEvenement === "SERVICE"
    ? "/services/index.php"
    : "/collectes/index.php";

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navbar.php";
?>

<style>
.offres-admin-container {
    max-width: 1250px;
}

.offre-summary {
    border: 0;
    border-radius: 18px;
    box-shadow: 0 5px 20px rgba(38,50,56,.08);
}

.jour-card {
    border: 1px solid #e4e9e5;
    border-radius: 16px;
    background: white;
    overflow: hidden;
}

.jour-header {
    background: #f4f9f3;
    padding: 16px 18px;
    border-bottom: 1px solid #e4e9e5;
}

.benevole-row {
    padding: 14px 18px;
    border-bottom: 1px solid #edf0ed;
}

.benevole-row:last-child {
    border-bottom: 0;
}

.avatar {
    width: 42px;
    min-width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #e8f5e9;
    color: #2E7D32;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.empty-box {
    border: 1px dashed #cfd8dc;
    border-radius: 16px;
    background: #fafcfa;
}

.affecte {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #e8f5e9;
    color: #256629;
    padding: 7px 11px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
}
</style>

<div class="container-fluid">
<div class="row">

<div class="col-md-2 p-0">
<?php include __DIR__ . "/../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">
<main class="offres-admin-container">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Réponses des bénévoles</h2>
        <p class="text-muted mb-0">
            Consultez les disponibilités et affectez les bénévoles à l'événement.
        </p>
    </div>

    <a href="<?= $retour ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>
        Retour
    </a>
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

<?php if ($offreSelectionnee): ?>

<div class="card offre-summary mb-4">
<div class="card-body p-4">

<div class="d-flex justify-content-between align-items-start gap-3">

<div>
    <span class="badge bg-primary mb-2">
        <?= htmlspecialchars($typeEvenement) ?>
    </span>

    <h3 class="mb-2">
        <?= htmlspecialchars(
            $offreSelectionnee["titre"] ?? "Offre bénévole"
        ) ?>
    </h3>

    <?php if (!empty($offreSelectionnee["description"])): ?>
        <p class="text-muted mb-3">
            <?= nl2br(
                htmlspecialchars(
                    $offreSelectionnee["description"]
                )
            ) ?>
        </p>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-3">
        <span>
            <i class="fa-solid fa-clock me-1"></i>
            <?= htmlspecialchars(
                $offreSelectionnee["heure_debut"] ?? ""
            ) ?>
            -
            <?= htmlspecialchars(
                $offreSelectionnee["heure_fin"] ?? ""
            ) ?>
        </span>

        <span>
            <i class="fa-solid fa-users me-1"></i>
            <?= $nbReponses ?>
            réponse<?= $nbReponses > 1 ? "s" : "" ?>
        </span>

        <?php if ($nombreBenevolesRequis > 0): ?>
        <span>
            <i class="fa-solid fa-user-check me-1"></i>
            <strong><?= $nombreAffectes ?> / <?= $nombreBenevolesRequis ?></strong>
            affecté<?= $nombreAffectes > 1 ? "s" : "" ?>
        </span>
        <?php endif; ?>
    </div>
</div>

<?php if ($offreComplete): ?>
<span class="badge bg-danger">
    COMPLET
</span>
<?php else: ?>
<span class="badge bg-success">
    <?= htmlspecialchars(
        $offreSelectionnee["statut"] ?? ""
    ) ?>
</span>
<?php endif; ?>

</div>
</div>
</div>

<?php if (empty($reponsesParJour)): ?>

<div class="empty-box text-center p-5">
    <i
        class="fa-solid fa-users fa-3x mb-3"
        style="color:#7BC96F;"
    ></i>

    <h4>Aucune réponse</h4>

    <p class="text-muted mb-0">
        Aucun bénévole n'a encore indiqué ses disponibilités pour cette offre.
    </p>
</div>

<?php else: ?>

<div class="row g-4">

<?php foreach ($reponsesParJour as $idJour => $jour): ?>

<?php
$dateAffichee = $jour["date_jour"];

if ($dateAffichee !== "") {
    $timestamp = strtotime($dateAffichee);

    if ($timestamp !== false) {
        $dateAffichee = date("d/m/Y", $timestamp);
    }
}
?>

<div class="col-lg-6">
<div class="jour-card h-100">

<div class="jour-header">
<div class="d-flex justify-content-between align-items-center">

<div>
    <h5 class="mb-1">
        <i class="fa-solid fa-calendar-day me-2"></i>
        <?= htmlspecialchars($dateAffichee) ?>
    </h5>

    <small class="text-muted">
        <?= htmlspecialchars(
            $offreSelectionnee["heure_debut"] ?? ""
        ) ?>
        -
        <?= htmlspecialchars(
            $offreSelectionnee["heure_fin"] ?? ""
        ) ?>
    </small>
</div>

<span class="badge bg-success">
    <?= count($jour["benevoles"]) ?>
    disponible<?= count($jour["benevoles"]) > 1 ? "s" : "" ?>
</span>

</div>
</div>

<?php foreach ($jour["benevoles"] as $benevole): ?>

<?php
$idUtilisateur = (int) $benevole["id_utilisateur"];
$estAffecte = isset($affectations[$idUtilisateur]);
?>

<div class="benevole-row">

<div class="d-flex align-items-center gap-3">

<div class="avatar">
    <?= htmlspecialchars(
        strtoupper(
            substr($benevole["prenom"], 0, 1) .
            substr($benevole["nom"], 0, 1)
        )
    ) ?>
</div>

<div class="flex-grow-1">

<div class="fw-semibold">
    <?= htmlspecialchars(
        trim(
            $benevole["prenom"] .
            " " .
            $benevole["nom"]
        )
    ) ?>
</div>

<div class="text-muted small">
    <?= htmlspecialchars($benevole["email"]) ?>
</div>

</div>

<?php if ($estAffecte): ?>

<span class="affecte">
    <i class="fa-solid fa-circle-check"></i>
    Affecté
</span>

<?php elseif ($offreComplete): ?>

<span class="badge bg-danger">
    <i class="fa-solid fa-ban me-1"></i>
    Offre complète
</span>

<?php elseif ($typeEvenement === "COLLECTE"): ?>

<form
    method="post"
    class="d-flex gap-2 align-items-center"
    onsubmit="return confirm('Affecter ce bénévole à cette collecte ?');"
>
    <input
        type="hidden"
        name="id_utilisateur"
        value="<?= $idUtilisateur ?>"
    >

    <select
        name="role_collecte"
        class="form-select form-select-sm"
        required
    >
        <option value="MANUTENTION">
            Manutention
        </option>

        <option value="CHAUFFEUR">
            Chauffeur
        </option>

        <option value="RESPONSABLE">
            Responsable
        </option>
    </select>

    <button
        type="submit"
        class="btn btn-success btn-sm"
    >
        <i class="fa-solid fa-user-check me-1"></i>
        Affecter
    </button>
</form>

<?php else: ?>

<form
    method="post"
    onsubmit="return confirm('Affecter ce bénévole à ce service ?');"
>
    <input
        type="hidden"
        name="id_utilisateur"
        value="<?= $idUtilisateur ?>"
    >

    <button
        type="submit"
        class="btn btn-success btn-sm"
    >
        <i class="fa-solid fa-user-check me-1"></i>
        Affecter
    </button>
</form>

<?php endif; ?>

</div>
</div>

<?php endforeach; ?>

</div>
</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

<?php endif; ?>

</main>
</div>

</div>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>