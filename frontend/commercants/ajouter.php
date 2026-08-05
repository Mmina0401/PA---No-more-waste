<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

include "../includes/header.php";
include "../includes/navbar.php";
require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    API::post("/api/utilisateurs/create", [
        "nom"              => $_POST["nom"],
        "prenom"           => $_POST["prenom"],
        "email"            => $_POST["email"],
        "mot_de_passe"     => $_POST["mot_de_passe"],
        "ville"            => $_POST["ville"],
        "role"             => "COMMERCANT",
        "raison_sociale"   => $_POST["raison_sociale"],
        "siret"            => $_POST["siret"],
        "secteur_activite" => $_POST["secteur_activite"],
    ]);

    header("Location: index.php");
    exit;
}
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Nouveau Commerçant</h2>

<hr>

<form method="post" class="col-md-6">

<div class="mb-3">
<label class="form-label">Raison sociale</label>
<input type="text" name="raison_sociale" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">SIRET</label>
<input type="text" name="siret" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Secteur d'activité</label>
<input type="text" name="secteur_activite" class="form-control">
</div>

<div class="mb-3">
<label class="form-label">Nom du contact</label>
<input type="text" name="nom" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Prénom du contact</label>
<input type="text" name="prenom" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Mot de passe</label>
<input type="password" name="mot_de_passe" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Ville</label>
<input type="text" name="ville" class="form-control">
</div>

<button type="submit" class="btn btn-success">Créer</button>
<a href="index.php" class="btn btn-secondary">Annuler</a>

</form>

</div>

</div>

</div>

<?php include "../includes/footer.php"; ?>