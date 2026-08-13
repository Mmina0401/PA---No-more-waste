<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$type = strtoupper(trim($_GET["type"] ?? ""));
$idEvenement = (int) ($_GET["id"] ?? 0);

$error = "";
$titre = "";
$description = "";
$nombreBenevoles = 1;
$evenement = null;

if (!in_array($type, ["COLLECTE", "SERVICE"], true)) {
    $error = "Type d'événement invalide.";
}

if ($idEvenement <= 0) {
    $error = "Événement invalide.";
}

if ($error === "") {
    if ($type === "COLLECTE") {
        $evenement = API::get(
            "/api/collectes/get?id=" . $idEvenement
        );

        if (
            !is_array($evenement) ||
            empty($evenement["id_collecte"])
        ) {
            $error = "Impossible de récupérer cette collecte.";
            $evenement = null;
        }
    }

    if ($type === "SERVICE") {
        $evenement = API::get(
            "/api/services/get?id=" . $idEvenement
        );

        if (
            !is_array($evenement) ||
            empty($evenement["id_service"])
        ) {
            $error = "Impossible de récupérer ce service.";
            $evenement = null;
        }
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    $evenement
) {
    $titre = trim($_POST["titre"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $nombreBenevoles = (int) (
        $_POST["nombre_benevoles_requis"] ?? 1
    );

    if ($titre === "") {
        $error = "Le titre de l'offre est obligatoire.";
    } elseif ($nombreBenevoles <= 0) {
        $error = "Le nombre de bénévoles doit être supérieur à 0.";
    } elseif ($nombreBenevoles > 100) {
        $error = "Le nombre de bénévoles demandé est trop élevé.";
    }

    if ($error === "") {
        $resultat = API::post(
            "/api/offres-benevoles/create",
            [
                "type_evenement" => $type,
                "id_evenement" => $idEvenement,
                "titre" => $titre,
                "description" => $description,
                "nombre_benevoles_requis" => $nombreBenevoles
            ]
        );

        if (
            is_array($resultat) &&
            isset($resultat["message"])
        ) {
            if ($type === "COLLECTE") {
                header(
                    "Location: /collectes/index.php?offre_creee=1"
                );
                exit;
            }

            if ($type === "SERVICE") {
                header(
                    "Location: /services/index.php?offre_creee=1"
                );
                exit;
            }
        }

        $error = is_array($resultat) && isset($resultat["error"])
            ? $resultat["error"]
            : "Impossible de créer l'offre bénévole.";
    }
}

$dateEvenement = "";
$heureDebut = "";
$heureFin = "";
$lieu = "";
$statut = "";

if ($evenement) {
    if ($type === "COLLECTE") {
        $dateEvenement = substr(
            $evenement["date_collecte"] ?? "",
            0,
            10
        );

        $lieu = trim(
            ($evenement["adresse"] ?? "") .
            " " .
            ($evenement["code_postal"] ?? "") .
            " " .
            ($evenement["ville"] ?? "")
        );
    }

    if ($type === "SERVICE") {
        $dateEvenement = substr(
            $evenement["date_service"] ?? "",
            0,
            10
        );

        $lieu = $evenement["lieu"] ?? "";
    }

    $heureDebut = substr(
        $evenement["heure_debut"] ?? "",
        0,
        5
    );

    $heureFin = substr(
        $evenement["heure_fin"] ?? "",
        0,
        5
    );

    $statut = $evenement["statut"] ?? "";
}

$nomType = $type === "SERVICE"
    ? "service"
    : "collecte";

$iconeType = $type === "SERVICE"
    ? "fa-hand-holding-heart"
    : "fa-truck";

$urlRetour = $type === "SERVICE"
    ? "/services/index.php"
    : "/collectes/index.php";

include "../includes/header.php";
include "../includes/navbar.php";
?>

<div class="container-fluid">
<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<div class="d-flex justify-content-between align-items-center mb-3">

<div>
    <h2 class="mb-1">
        Créer une offre bénévoles
    </h2>

    <p class="text-muted mb-0">
        Publiez un besoin de bénévoles pour ce
        <?= htmlspecialchars($nomType) ?>.
    </p>
</div>

<a
    href="<?= htmlspecialchars($urlRetour) ?>"
    class="btn btn-outline-secondary"
>
    <i class="fa-solid fa-arrow-left me-1"></i>
    Retour
</a>

</div>

<hr>

<?php if ($error): ?>

<div class="alert alert-danger">
    <i class="fa-solid fa-circle-exclamation me-2"></i>
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<?php if ($evenement): ?>

<div class="card shadow-sm mb-4">
<div class="card-body">

<h5 class="mb-3">

<i class="fa-solid <?= $iconeType ?> me-2"></i>

<?php if ($type === "COLLECTE"): ?>

    Collecte #<?= $idEvenement ?>

<?php else: ?>

    <?= htmlspecialchars(
        $evenement["nom"] ?? "Service #" . $idEvenement
    ) ?>

<?php endif; ?>

</h5>

<div class="row g-3">

<div class="col-md-3">

<small class="text-muted d-block">
    Date
</small>

<strong>

<?php
if ($dateEvenement !== "") {
    $timestamp = strtotime($dateEvenement);

    echo htmlspecialchars(
        $timestamp
            ? date("d/m/Y", $timestamp)
            : $dateEvenement
    );
} else {
    echo "—";
}
?>

</strong>

</div>

<div class="col-md-3">

<small class="text-muted d-block">
    Horaires
</small>

<strong>
    <?= htmlspecialchars($heureDebut ?: "—") ?>
    -
    <?= htmlspecialchars($heureFin ?: "—") ?>
</strong>

</div>

<div class="col-md-3">

<small class="text-muted d-block">
    Lieu
</small>

<strong>
    <?= htmlspecialchars($lieu ?: "—") ?>
</strong>

</div>

<div class="col-md-3">

<small class="text-muted d-block">
    Statut
</small>

<span class="badge bg-success">
    <?= htmlspecialchars($statut ?: "—") ?>
</span>

</div>

</div>

<?php if (
    $type === "SERVICE" &&
    isset($evenement["capacite_max"]) &&
    $evenement["capacite_max"] !== null
): ?>

<div class="mt-3">

<small class="text-muted d-block">
    Capacité du service
</small>

<span>
    <?= (int) ($evenement["nb_inscrits"] ?? 0) ?>
    /
    <?= (int) $evenement["capacite_max"] ?>
    participant(s)
</span>

</div>

<?php endif; ?>

</div>
</div>

<div class="card shadow-sm">
<div class="card-body">

<form method="post">

<div class="mb-3">

<label
    for="titre"
    class="form-label fw-semibold"
>
    Titre de l'offre
</label>

<input
    type="text"
    id="titre"
    name="titre"
    class="form-control"
    maxlength="150"
    value="<?= htmlspecialchars($titre) ?>"
    placeholder="<?=
        $type === "SERVICE"
            ? "Ex : Recherche bénévoles pour préparer les paniers"
            : "Ex : Recherche bénévoles pour une collecte"
    ?>"
    required
>

</div>

<div class="mb-3">

<label
    for="description"
    class="form-label fw-semibold"
>
    Description de la mission
</label>

<textarea
    id="description"
    name="description"
    class="form-control"
    rows="4"
    maxlength="1000"
    placeholder="<?=
        $type === "SERVICE"
            ? "Ex : Préparation et organisation des paniers alimentaires."
            : "Ex : Aide au chargement, tri des produits et organisation de la collecte."
    ?>"
><?= htmlspecialchars($description) ?></textarea>

</div>

<div class="mb-4">

<label
    for="nombre_benevoles_requis"
    class="form-label fw-semibold"
>
    Nombre de bénévoles recherchés
</label>

<input
    type="number"
    id="nombre_benevoles_requis"
    name="nombre_benevoles_requis"
    class="form-control"
    min="1"
    max="100"
    value="<?= (int) $nombreBenevoles ?>"
    required
>

<div class="form-text">
    Indiquez combien de bénévoles sont nécessaires pour ce
    <?= htmlspecialchars($nomType) ?>.
</div>

</div>

<div class="alert alert-light border">

<div class="d-flex gap-3">

<i class="fa-solid fa-circle-info mt-1 text-primary"></i>

<div>

<strong>
    Date et horaires automatiques
</strong>

<div class="text-muted mt-1">

L'offre utilisera automatiquement la date

<strong>
    <?php
    if ($dateEvenement !== "") {
        $timestamp = strtotime($dateEvenement);

        echo htmlspecialchars(
            $timestamp
                ? date("d/m/Y", $timestamp)
                : $dateEvenement
        );
    } else {
        echo "—";
    }
    ?>
</strong>

et les horaires

<strong>
    <?= htmlspecialchars($heureDebut ?: "—") ?>
    -
    <?= htmlspecialchars($heureFin ?: "—") ?>
</strong>

du <?= htmlspecialchars($nomType) ?>.

</div>

</div>
</div>
</div>

<div class="d-flex gap-2 mt-4">

<button
    type="submit"
    class="btn btn-success"
>
    <i class="fa-solid fa-bullhorn me-1"></i>
    Publier l'offre
</button>

<a
    href="<?= htmlspecialchars($urlRetour) ?>"
    class="btn btn-secondary"
>
    Annuler
</a>

</div>

</form>

</div>
</div>

<?php endif; ?>

</div>
</div>
</div>

<?php include "../includes/footer.php"; ?>