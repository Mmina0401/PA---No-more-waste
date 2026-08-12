<?php
session_start();

require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    API::post("/api/collectes/create", [

        "id_utilisateur" => (int)$_POST["id_utilisateur"],
        "id_vehicule"    => (int)$_POST["id_vehicule"],
        "date_collecte"  => $_POST["date_collecte"],
        "heure_debut"    => $_POST["heure_debut"],
        "heure_fin"      => $_POST["heure_fin"],
        "adresse"        => trim($_POST["adresse"]),
        "ville"          => trim($_POST["ville"]),
        "code_postal"    => trim($_POST["code_postal"]),
        "commentaire"    => trim($_POST["commentaire"]),
        "statut"         => $_POST["statut"]

    ]);

    echo "<script>window.location='index.php';</script>";
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

<h2>Ajouter une collecte</h2>

<hr>

<form method="post">

<div class="mb-3">
<label class="form-label">ID Utilisateur</label>
<input
type="number"
name="id_utilisateur"
class="form-control"
required>
</div>

<div class="mb-3">
<label class="form-label">ID Véhicule</label>
<input
type="number"
name="id_vehicule"
class="form-control"
required>
</div>

<div class="mb-3">
<label class="form-label">Date</label>
<input
type="date"
name="date_collecte"
class="form-control"
required>
</div>

<div class="row">

<div class="col">
<label class="form-label">Heure début</label>
<input
type="time"
name="heure_debut"
class="form-control"
required>
</div>

<div class="col">
<label class="form-label">Heure fin</label>
<input
type="time"
name="heure_fin"
class="form-control"
required>
</div>

</div>

<br>

<div class="mb-3">
<label class="form-label">Adresse</label>
<input
type="text"
name="adresse"
class="form-control"
required>
</div>

<div class="mb-3">
<label class="form-label">Ville</label>
<input
type="text"
name="ville"
class="form-control"
required>
</div>

<div class="mb-3">
<label class="form-label">Code postal</label>
<input
type="text"
name="code_postal"
class="form-control"
required>
</div>

<div class="mb-3">
<label class="form-label">Commentaire</label>
<textarea
name="commentaire"
class="form-control"
rows="4"></textarea>
</div>

<div class="mb-3">
<label class="form-label">Statut</label>

<select
name="statut"
class="form-select">

<option value="EN_ATTENTE">En attente</option>
<option value="PLANIFIEE">Planifiée</option>
<option value="TERMINEE">Terminée</option>
<option value="ANNULEE">Annulée</option>

</select>

</div>

<button class="btn btn-success">
Créer la collecte
</button>

<a href="index.php" class="btn btn-secondary">
Annuler
</a>

</form>

</div>

</div>

</div>

<?php include "../includes/footer.php"; ?>