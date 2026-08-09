<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$id = $_GET["id"] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payload = [
        "id_service"   => (int) $id,
        "nom"          => trim($_POST["nom"]),
        "description"  => trim($_POST["description"]) ?: null,
        "lieu"         => trim($_POST["lieu"]) ?: null,
        "date_service" => $_POST["date_service"] ?: null,
        "heure_debut"  => $_POST["heure_debut"] ?: null,
        "heure_fin"    => $_POST["heure_fin"] ?: null,
        "capacite_max" => $_POST["capacite_max"] !== "" ? (int) $_POST["capacite_max"] : null,
        "statut"       => $_POST["statut"],
    ];

    API::post("/api/services/update", $payload);

    header("Location: index.php");
    exit;
}

$s = API::get("/api/services/get?id=" . urlencode($id));

include "../includes/header.php";
include "../includes/navbar.php";

if (!$s || isset($s["error"])) {
    echo "<div class='container p-4'><div class='alert alert-danger'>Service introuvable.</div></div>";
    include "../includes/footer.php";
    exit;
}
?>

<div class="container-fluid">
<div class="row">
<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Modifier le Service</h2>
<hr>

<form method="post" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Nom du service</label>
        <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($s["nom"]) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"><?= htmlspecialchars($s["description"] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Lieu</label>
        <input type="text" name="lieu" class="form-control" value="<?= htmlspecialchars($s["lieu"] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Date</label>
        <input type="date" name="date_service" class="form-control" value="<?= htmlspecialchars($s["date_service"] ?? '') ?>">
    </div>

    <div class="row">
        <div class="col mb-3">
            <label class="form-label">Heure de début</label>
            <input type="time" name="heure_debut" class="form-control" value="<?= htmlspecialchars(substr($s["heure_debut"] ?? '', 0, 5)) ?>">
        </div>

        <div class="col mb-3">
            <label class="form-label">Heure de fin</label>
            <input type="time" name="heure_fin" class="form-control" value="<?= htmlspecialchars(substr($s["heure_fin"] ?? '', 0, 5)) ?>">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Capacité maximale</label>
        <input type="number" name="capacite_max" class="form-control" min="1" value="<?= htmlspecialchars($s["capacite_max"] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
            <option value="OUVERT" <?= $s["statut"] === "OUVERT" ? "selected" : "" ?>>Ouvert</option>
            <option value="COMPLET" <?= $s["statut"] === "COMPLET" ? "selected" : "" ?>>Complet</option>
            <option value="ANNULE" <?= $s["statut"] === "ANNULE" ? "selected" : "" ?>>Annulé</option>
        </select>
    </div>

    <button type="submit" class="btn btn-success">Enregistrer</button>
    <a href="index.php" class="btn btn-secondary">Annuler</a>
</form>

</div>
</div>
</div>

<?php include "../includes/footer.php"; ?>