<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "marquer") {
    API::post("/api/rappels/marquer", ["id_adhesion" => (int) $_POST["id_adhesion"]]);
    header("Location: index.php");
    exit;
}

include "../includes/header.php";
include "../includes/navbar.php";

$adhesionsARelancer = API::get("/api/rappels/a-envoyer");
if (!is_array($adhesionsARelancer)) $adhesionsARelancer = [];

function badgeUrgence($joursRestants)
{
    if ($joursRestants <= 7)  return '<span class="badge bg-danger">' . $joursRestants . ' j</span>';
    if ($joursRestants <= 15) return '<span class="badge bg-warning text-dark">' . $joursRestants . ' j</span>';
    return '<span class="badge bg-secondary">' . $joursRestants . ' j</span>';
}
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Relances d'adhésion</h2>
<p class="text-muted">Adhésions qui expirent dans moins de 30 jours et pas encore relancées.</p>

<hr>

<?php if (empty($adhesionsARelancer)): ?>
    <div class="alert alert-light border">Aucune relance à faire pour le moment.</div>
<?php else: ?>

<table class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
<th>Commerçant</th>
<th>Email</th>
<th>Expire le</th>
<th>Jours restants</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach ($adhesionsARelancer as $a): ?>
<tr>

<td><?= htmlspecialchars($a["nom_commercant"]) ?></td>

<td><?= htmlspecialchars($a["email"]) ?></td>

<td><?= htmlspecialchars($a["date_fin"]) ?></td>

<td><?= badgeUrgence($a["jours_restants"]) ?></td>

<td>

<a href="mailto:<?= htmlspecialchars($a["email"]) ?>?subject=Renouvellement de votre adhésion No More Waste"
class="btn btn-outline-success btn-sm">
Écrire un email
</a>

<form method="post" style="display:inline;">
<input type="hidden" name="action" value="marquer">
<input type="hidden" name="id_adhesion" value="<?= $a["id_adhesion"] ?>">
<button type="submit" class="btn btn-secondary btn-sm">Marquer comme relancé</button>
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