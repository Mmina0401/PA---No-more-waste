<?php
session_start();


require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$type = strtoupper(trim($_GET["type"] ?? ""));
$idEvenement = (int) ($_GET["id"] ?? 0);

$message = "";
$error = "";

$titre = "";
$description = "";
$nombreBenevoles = 1;

if ($type !== "COLLECTE") {
    $error = "Pour le moment, ce formulaire est réservé aux collectes.";
}

if ($idEvenement <= 0) {
    $error = "Collecte invalide.";
}

$collecte = null;

if ($error === "") {
    $collecte = API::get(
        "/api/collectes/get?id=" . $idEvenement
    );

    if (!is_array($collecte) || empty($collecte["id_collecte"])) {
        $error = "Impossible de récupérer cette collecte.";
        $collecte = null;
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    $collecte
) {
    $titre = trim($_POST["titre"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $nombreBenevoles = (int) ($_POST["nombre_benevoles_requis"] ?? 1);

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
                "type_evenement" => "COLLECTE",
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
            header(
                "Location: /collectes/index.php?offre_creee=1"
            );
            exit;
        }

        $error = is_array($resultat) && isset($resultat["error"])
            ? $resultat["error"]
            : "Impossible de créer l'offre bénévole.";
    }
}

include "../includes/header.php";
include "../includes/navbar.php";

$dateCollecte = "";

if ($collecte) {
    $dateCollecte = substr(
        $collecte["date_collecte"] ?? "",
        0,
        10
    );
}

$heureDebut = $collecte["heure_debut"] ?? "";
$heureFin = $collecte["heure_fin"] ?? "";

$heureDebut = substr($heureDebut, 0, 5);
$heureFin = substr($heureFin, 0, 5);
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
            Publiez un besoin de bénévoles pour cette collecte.
        </p>
    </div>

    <a
        href="/collectes/index.php"
        class="btn btn-outline-secondary"
    >
        <i class="fa-solid fa-arrow-left me-1"></i>
        Retour
    </a>
</div>

<hr>

<?php if ($message): ?>
<div class="alert alert-success">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if ($collecte): ?>

<div class="card shadow-sm mb-4">
<div class="card-body">

<h5 class="mb-3">
    <i class="fa-solid fa-truck me-2"></i>
    Collecte #<?= $idEvenement ?>
</h5>

<div class="row g-3">

<div class="col-md-3">
    <small class="text-muted d-block">
        Date
    </small>

    <strong>
        <?php
        if ($dateCollecte !== "") {
            $timestamp = strtotime($dateCollecte);

            echo htmlspecialchars(
                $timestamp
                    ? date("d/m/Y", $timestamp)
                    : $dateCollecte
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
        Ville
    </small>

    <strong>
        <?= htmlspecialchars(
            $collecte["ville"] ?? "—"
        ) ?>
    </strong>
</div>

<div class="col-md-3">
    <small class="text-muted d-block">
        Statut
    </small>

    <span class="badge bg-success">
        <?= htmlspecialchars(
            $collecte["statut"] ?? "—"
        ) ?>
    </span>
</div>

</div>

<?php if (!empty($collecte["adresse"])): ?>

<div class="mt-3">
    <small class="text-muted d-block">
        Adresse
    </small>

    <span>
        <?= htmlspecialchars($collecte["adresse"]) ?>

        <?php if (!empty($collecte["code_postal"])): ?>
            ,
            <?= htmlspecialchars($collecte["code_postal"]) ?>
        <?php endif; ?>

        <?= htmlspecialchars($collecte["ville"] ?? "") ?>
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
    placeholder="Ex : Recherche bénévoles pour collecte Carrefour"
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
    placeholder="Ex : Aide au chargement, tri des produits et organisation de la collecte."
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
    Indiquez combien de bénévoles sont nécessaires pour cette collecte.
</div>

</div>

<div class="alert alert-light border">

<div class="d-flex gap-3">

<i class="fa-solid fa-circle-info mt-1 text-primary"></i>

<div>
    <strong>Date et horaires automatiques</strong>

    <div class="text-muted mt-1">
        L'offre utilisera automatiquement la date
        <strong><?= htmlspecialchars($dateCollecte) ?></strong>
        et les horaires
        <strong>
            <?= htmlspecialchars($heureDebut) ?>
            -
            <?= htmlspecialchars($heureFin) ?>
        </strong>
        de cette collecte.
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
    href="/collectes/index.php"
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