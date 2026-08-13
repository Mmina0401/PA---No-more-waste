<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = (int) $_POST["id"];

    if ($action === "valider") {
        $candidat = API::get("/api/utilisateurs/get?id=" . $id);

        API::post("/api/utilisateurs/update", [
            "id_utilisateur" => $id,
            "nom"            => $candidat["nom"],
            "prenom"         => $candidat["prenom"],
            "email"          => $candidat["email"],
            "telephone"      => $candidat["telephone"] ?? null,
            "adresse"        => $candidat["adresse"] ?? null,
            "ville"          => $candidat["ville"] ?? null,
            "code_postal"    => $candidat["code_postal"] ?? null,
            "role"           => "BENEVOLE",
            "actif"          => true,
        ]);

    } elseif ($action === "refuser") {
        API::post("/api/utilisateurs/delete", [
            "id_utilisateur" => $id
        ]);
    }

    header("Location: index.php");
    exit;
}

include "../includes/header.php";
include "../includes/navbar.php";

$tousLesBenevoles = API::get("/api/utilisateurs?role=BENEVOLE");

if (!is_array($tousLesBenevoles)) {
    $tousLesBenevoles = [];
}

$candidatures = array_filter(
    $tousLesBenevoles,
    fn($u) => !$u["actif"]
);
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Candidatures bénévoles</h2>
<p class="text-muted">Personnes ayant candidaté depuis le site public, en attente de validation.</p>

<hr>

<?php if (empty($candidatures)): ?>
    <div class="alert alert-light border">Aucune candidature en attente.</div>
<?php else: ?>

<table class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
<th>Nom</th>
<th>Email</th>
<th>Téléphone</th>
<th>Ville</th>
<th>Reçue le</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach ($candidatures as $c): ?>
<tr>

<td><?= htmlspecialchars($c["prenom"] . " " . $c["nom"]) ?></td>

<td><?= htmlspecialchars($c["email"]) ?></td>

<td><?= htmlspecialchars($c["telephone"] ?? "—") ?></td>

<td><?= htmlspecialchars($c["ville"] ?? "—") ?></td>

<td><?= substr($c["date_creation"], 0, 10) ?></td>

<td>

<form method="post" style="display:inline;">
<input type="hidden" name="action" value="valider">
<input type="hidden" name="id" value="<?= $c["id_utilisateur"] ?>">
<button type="submit" class="btn btn-success btn-sm">Valider</button>
</form>

<form method="post" style="display:inline;" onsubmit="return confirm('Refuser et supprimer cette candidature ?');">
<input type="hidden" name="action" value="refuser">
<input type="hidden" name="id" value="<?= $c["id_utilisateur"] ?>">
<button type="submit" class="btn btn-danger btn-sm">Refuser</button>
</form>

</td>

</tr>
<?php endforeach; ?>

</tbody>

</table>

<?php endif; ?>

</div>

</div>

</div>

<?php include "../includes/footer.php"; ?>