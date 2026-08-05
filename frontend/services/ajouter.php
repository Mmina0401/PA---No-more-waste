<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

include "../includes/header.php";
include "../includes/navbar.php";
require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payload = [
        "nom"          => trim($_POST["nom"]),
        "description"  => trim($_POST["description"]) ?: null,
        "lieu"         => trim($_POST["lieu"]) ?: null,
        "date_service" => $_POST["date_service"] ?: null,
        "heure_debut"  => $_POST["heure_debut"] ?: null,
        "heure_fin"    => $_POST["heure_fin"] ?: null,
        "capacite_max" => $_POST["capacite_max"] !== "" ? (int) $_POST["capacite_max"] : null,
        "statut"       => "OUVERT",
    ];

    API::post("/api/services/create", $payload);
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

<h2>Nouveau Service</h2>
<hr>

<form method="post" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Nom du service</label>
        <input type="text" name="nom" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Lieu</label>
        <input type="text" name="lieu" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Date</label>
        <input type="date" name="date_service" class="form-control">
    </div>
    <div class="row">
        <div class="col mb-3">
            <label class="form-label">Heure de début</label>
            <input type="time" name="heure_debut" class="form-control">
        </div>
        <div class="col mb-3">
            <label class="form-label">Heure de fin</label>
            <input type="time" name="heure_fin" class="form-control">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Capacité maximale (laisser vide = illimité)</label>
        <input type="number" name="capacite_max" class="form-control" min="1">
    </div>

    <button type="submit" class="btn btn-success">Créer le service</button>
    <a href="index.php" class="btn btn-secondary">Annuler</a>
</form>

</div>
</div>
</div>

<?php include "../includes/footer.php"; ?>