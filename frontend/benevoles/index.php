<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$message = "";
$error = "";

$competencesDisponibles = [
    1 => "Chauffeur",
    2 => "Cuisine",
    3 => "Plomberie",
    4 => "Électricité",
    5 => "Bricolage",
    6 => "Logistique",
    7 => "Manutention"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idUtilisateur = (int) ($_POST["id_utilisateur"] ?? 0);
    $nom = trim($_POST["nom"] ?? "");
    $prenom = trim($_POST["prenom"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $ville = trim($_POST["ville"] ?? "");
    $actif = isset($_POST["actif"]);

    $competences = $_POST["competences"] ?? [];
    $competences = array_map("intval", $competences);

    $resultat = API::post("/api/benevoles/update", [
        "id_utilisateur" => $idUtilisateur,
        "nom" => $nom,
        "prenom" => $prenom,
        "email" => $email,
        "telephone" => $telephone,
        "ville" => $ville,
        "actif" => $actif,
        "competences" => $competences
    ]);

    if (is_array($resultat) && isset($resultat["message"])) {
        $message = $resultat["message"];
    } else {
        $error = "Impossible de modifier le bénévole.";
    }
}

$benevoles = API::get("/api/benevoles");

if (!is_array($benevoles)) {
    $benevoles = [];
    $error = "Impossible de récupérer les bénévoles.";
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

<h2>Gestion des bénévoles</h2>
<hr>

<p class="text-muted">
    Consultez et modifiez les informations, compétences et statuts des bénévoles.
</p>

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

<?php if (empty($benevoles)): ?>

    <div class="alert alert-light border">
        Aucun bénévole enregistré pour le moment.
    </div>

<?php else: ?>

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead class="table-dark">
<tr>
    <th>Nom</th>
    <th>Prénom</th>
    <th>Email</th>
    <th>Téléphone</th>
    <th>Ville</th>
    <th>Statut</th>
    <th>Compétences</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach ($benevoles as $benevole): ?>

<tr>

<td>
    <?= htmlspecialchars($benevole["nom"] ?? "") ?>
</td>

<td>
    <?= htmlspecialchars($benevole["prenom"] ?? "") ?>
</td>

<td>
    <?= htmlspecialchars($benevole["email"] ?? "") ?>
</td>

<td>
    <?= htmlspecialchars($benevole["telephone"] ?? "—") ?>
</td>

<td>
    <?= htmlspecialchars($benevole["ville"] ?? "—") ?>
</td>

<td>

<?php if (!empty($benevole["actif"])): ?>

    <span class="badge bg-success">
        Actif
    </span>

<?php else: ?>

    <span class="badge bg-warning text-dark">
        En attente
    </span>

<?php endif; ?>

</td>

<td>

<?php if (!empty($benevole["competences"])): ?>

    <?php foreach ($benevole["competences"] as $competence): ?>

        <span class="badge bg-info text-dark me-1 mb-1">
            <?= htmlspecialchars($competence) ?>
        </span>

    <?php endforeach; ?>

<?php else: ?>

    <span class="text-muted">
        Aucune
    </span>

<?php endif; ?>

</td>

<td>

<button
    type="button"
    class="btn btn-warning btn-sm"
    onclick='ouvrirModification(
        <?= json_encode(
            $benevole,
            JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?>
    )'
>
    Modifier
</button>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>

</div>
</div>
</div>

<div
    class="modal fade"
    id="editModal"
    tabindex="-1"
    aria-hidden="true"
>

<div class="modal-dialog">

<div class="modal-content">

<form method="POST">

<div class="modal-header">

<h5 class="modal-title">
    Modifier le bénévole
</h5>

<button
    type="button"
    class="btn-close"
    data-bs-dismiss="modal"
></button>

</div>

<div class="modal-body">

<input
    type="hidden"
    name="id_utilisateur"
    id="id_utilisateur"
>

<div class="mb-3">

<label class="form-label">
    Nom
</label>

<input
    type="text"
    class="form-control"
    name="nom"
    id="nom"
    required
>

</div>

<div class="mb-3">

<label class="form-label">
    Prénom
</label>

<input
    type="text"
    class="form-control"
    name="prenom"
    id="prenom"
    required
>

</div>

<div class="mb-3">

<label class="form-label">
    Email
</label>

<input
    type="email"
    class="form-control"
    name="email"
    id="email"
    required
>

</div>

<div class="mb-3">

<label class="form-label">
    Téléphone
</label>

<input
    type="text"
    class="form-control"
    name="telephone"
    id="telephone"
>

</div>

<div class="mb-3">

<label class="form-label">
    Ville
</label>

<input
    type="text"
    class="form-control"
    name="ville"
    id="ville"
>

</div>

<div class="form-check mb-3">

<input
    type="checkbox"
    class="form-check-input"
    name="actif"
    id="actif"
>

<label
    class="form-check-label"
    for="actif"
>
    Bénévole actif
</label>

</div>

<div class="mb-3">

<label class="form-label">
    Compétences
</label>

<?php foreach ($competencesDisponibles as $id => $nom): ?>

<div class="form-check">

<input
    type="checkbox"
    class="form-check-input competence-checkbox"
    name="competences[]"
    value="<?= $id ?>"
    id="competence_<?= $id ?>"
    data-nom="<?= htmlspecialchars($nom) ?>"
>

<label
    class="form-check-label"
    for="competence_<?= $id ?>"
>
    <?= htmlspecialchars($nom) ?>
</label>

</div>

<?php endforeach; ?>

</div>

</div>

<div class="modal-footer">

<button
    type="button"
    class="btn btn-secondary"
    data-bs-dismiss="modal"
>
    Annuler
</button>

<button
    type="submit"
    class="btn btn-success"
>
    Enregistrer
</button>

</div>

</form>

</div>
</div>
</div>

<script>

function ouvrirModification(benevole) {

    document.getElementById("id_utilisateur").value =
        benevole.id_utilisateur ?? "";

    document.getElementById("nom").value =
        benevole.nom ?? "";

    document.getElementById("prenom").value =
        benevole.prenom ?? "";

    document.getElementById("email").value =
        benevole.email ?? "";

    document.getElementById("telephone").value =
        benevole.telephone ?? "";

    document.getElementById("ville").value =
        benevole.ville ?? "";

    document.getElementById("actif").checked =
        benevole.actif === true;

    const checkboxes =
        document.querySelectorAll(".competence-checkbox");

    checkboxes.forEach(function(checkbox) {

        checkbox.checked = false;

        if (
            Array.isArray(benevole.competences) &&
            benevole.competences.includes(
                checkbox.dataset.nom
            )
        ) {
            checkbox.checked = true;
        }

    });

    const modal = new bootstrap.Modal(
        document.getElementById("editModal")
    );

    modal.show();
}

</script>

<?php include "../includes/footer.php"; ?>