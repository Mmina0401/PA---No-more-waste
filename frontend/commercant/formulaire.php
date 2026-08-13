<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$id = $_GET["id"] ?? null;
$c = [
    "nom" => "", "prenom" => "", "email" => "", "telephone" => "",
    "adresse" => "", "ville" => "", "code_postal" => "",
    "raison_sociale" => "", "siret" => "", "secteur_activite" => "", "actif" => true
];

if ($id) {
    $c = API::get("/api/utilisateurs/get?id=" . $id);
    if (!$c || isset($c["error"])) {
        header("Location: index.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $payload = [
        "nom"              => $_POST["nom"],
        "prenom"           => $_POST["prenom"],
        "email"            => $_POST["email"],
        "telephone"        => $_POST["telephone"],
        "adresse"          => $_POST["adresse"],
        "ville"            => $_POST["ville"],
        "code_postal"      => $_POST["code_postal"],
        "role"             => "COMMERCANT",
        "raison_sociale"   => $_POST["raison_sociale"],
        "siret"            => $_POST["siret"],
        "secteur_activite" => $_POST["secteur_activite"],
    ];

    if ($id) {
        $payload["id_utilisateur"] = (int) $id;
        $payload["actif"] = isset($_POST["actif"]);
        API::post("/api/utilisateurs/update", $payload);
    } else {
        $payload["mot_de_passe"] = $_POST["mot_de_passe"];
        API::post("/api/utilisateurs/create", $payload);
    }

    header("Location: index.php");
    exit;
}

include "../includes/header.php";
include "../includes/navbar.php";
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2><?= $id ? "Modifier le Commerçant" : "Nouveau Commerçant" ?></h2>

<hr>

<form method="post" class="col-md-6">

<div class="mb-3">
<label class="form-label">Raison sociale</label>
<input type="text" name="raison_sociale" class="form-control" required
value="<?= htmlspecialchars($c["raison_sociale"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">SIRET</label>
<input type="text" name="siret" class="form-control" required maxlength="14" pattern="[0-9]{14}"
value="<?= htmlspecialchars($c["siret"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Secteur d'activité</label>
<input type="text" name="secteur_activite" class="form-control"
value="<?= htmlspecialchars($c["secteur_activite"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Nom du contact</label>
<input type="text" name="nom" class="form-control" required
value="<?= htmlspecialchars($c["nom"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Prénom du contact</label>
<input type="text" name="prenom" class="form-control" required
value="<?= htmlspecialchars($c["prenom"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" required
value="<?= htmlspecialchars($c["email"] ?? "") ?>">
</div>

<?php if (!$id): ?>
<div class="mb-3">
<label class="form-label">Mot de passe</label>
<input type="password" name="mot_de_passe" class="form-control" required>
</div>
<?php endif; ?>

<div class="mb-3">
<label class="form-label">Téléphone</label>
<input type="text" name="telephone" class="form-control"
value="<?= htmlspecialchars($c["telephone"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Adresse</label>
<input type="text" name="adresse" class="form-control"
value="<?= htmlspecialchars($c["adresse"] ?? "") ?>">
</div>

<div class="row">
<div class="col-md-8">
<div class="mb-3">
<label class="form-label">Ville</label>
<input type="text" name="ville" class="form-control"
value="<?= htmlspecialchars($c["ville"] ?? "") ?>">
</div>
</div>

<div class="col-md-4">
<div class="mb-3">
<label class="form-label">Code postal</label>
<input type="text" name="code_postal" class="form-control"
value="<?= htmlspecialchars($c["code_postal"] ?? "") ?>">
</div>
</div>
</div>

<?php if ($id): ?>
<div class="mb-3 form-check">
<input type="checkbox" name="actif" class="form-check-input" id="actif" <?= $c["actif"] ? "checked" : "" ?>>
<label class="form-check-label" for="actif">Compte actif</label>
</div>
<?php endif; ?>

<button type="submit" class="btn btn-success"><?= $id ? "Enregistrer" : "Créer" ?></button>
<a href="index.php" class="btn btn-secondary">Annuler</a>

</form>

</div>

</div>

</div>

<?php include "../includes/footer.php"; ?>