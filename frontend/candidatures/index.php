<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = (int) ($_POST["id"] ?? 0);

    if ($action === "valider") {

        // L'admin doit confirmer qu'il a vérifié les compétences déclarées.
        if (!isset($_POST["competences_verifiees"])) {
            header("Location: index.php?erreur=competences");
            exit;
        }

        // Vérification côté serveur qu'au moins une compétence est enregistrée.
        $benevoles = API::get("/api/benevoles");
        $competencesCandidat = [];

        if (is_array($benevoles)) {
            foreach ($benevoles as $benevole) {
                if ((int) ($benevole["id_utilisateur"] ?? 0) === $id) {
                    $competencesCandidat = $benevole["competences"] ?? [];
                    break;
                }
            }
        }

        if (empty($competencesCandidat)) {
            header("Location: index.php?erreur=aucune_competence");
            exit;
        }

        $candidat = API::get("/api/utilisateurs/get?id=" . $id);

        if (!is_array($candidat) || empty($candidat)) {
            header("Location: index.php?erreur=candidat");
            exit;
        }

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
    fn($u) => !($u["actif"] ?? false)
);

// /api/benevoles contient les compétences associées à chaque bénévole.
$benevolesAvecCompetences = API::get("/api/benevoles");

if (!is_array($benevolesAvecCompetences)) {
    $benevolesAvecCompetences = [];
}

$competencesParBenevole = [];

foreach ($benevolesAvecCompetences as $benevole) {
    $idBenevole = (int) ($benevole["id_utilisateur"] ?? 0);
    $competencesParBenevole[$idBenevole] = $benevole["competences"] ?? [];
}
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Candidatures bénévoles</h2>
<p class="text-muted">
    Personnes ayant candidaté depuis le site public, en attente de validation.
    Vérifiez leurs compétences déclarées avant de les accepter.
</p>

<hr>

<?php if (isset($_GET["erreur"])): ?>
    <div class="alert alert-danger">
        <?php if ($_GET["erreur"] === "competences"): ?>
            Vous devez confirmer avoir vérifié les compétences avant de valider la candidature.
        <?php elseif ($_GET["erreur"] === "aucune_competence"): ?>
            Cette candidature ne possède aucune compétence enregistrée et ne peut pas être validée.
        <?php else: ?>
            Impossible de récupérer cette candidature.
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (empty($candidatures)): ?>

    <div class="alert alert-light border">
        Aucune candidature en attente.
    </div>

<?php else: ?>

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead class="table-dark">
<tr>
<th>Nom</th>
<th>Email</th>
<th>Téléphone</th>
<th>Ville</th>
<th>Compétences déclarées</th>
<th>Reçue le</th>
<th style="min-width: 220px;">Actions</th>
</tr>
</thead>

<tbody>

<?php foreach ($candidatures as $c): ?>

<?php
$idCandidat = (int) $c["id_utilisateur"];
$competences = $competencesParBenevole[$idCandidat] ?? [];
?>

<tr>

<td><?= htmlspecialchars($c["prenom"] . " " . $c["nom"]) ?></td>

<td><?= htmlspecialchars($c["email"]) ?></td>

<td><?= htmlspecialchars($c["telephone"] ?? "—") ?></td>

<td><?= htmlspecialchars($c["ville"] ?? "—") ?></td>

<td>
    <?php if (empty($competences)): ?>

        <span class="badge bg-danger">
            Aucune compétence
        </span>

    <?php else: ?>

        <?php foreach ($competences as $competence): ?>
            <span class="badge bg-light text-dark border me-1 mb-1">
                <?= htmlspecialchars($competence) ?>
            </span>
        <?php endforeach; ?>

    <?php endif; ?>
</td>

<td><?= substr($c["date_creation"], 0, 10) ?></td>

<td>

<?php if (!empty($competences)): ?>

    <form method="post" class="mb-2">
        <input type="hidden" name="action" value="valider">
        <input type="hidden" name="id" value="<?= $idCandidat ?>">

        <div class="form-check mb-2">
            <input
                class="form-check-input"
                type="checkbox"
                name="competences_verifiees"
                value="1"
                id="verif_<?= $idCandidat ?>"
                required
            >
            <label
                class="form-check-label small"
                for="verif_<?= $idCandidat ?>"
            >
                Compétences vérifiées
            </label>
        </div>

        <button type="submit" class="btn btn-success btn-sm">
            Valider la candidature
        </button>
    </form>

<?php else: ?>

    <div class="text-danger small mb-2">
        Validation impossible sans compétence.
    </div>

<?php endif; ?>

<form
    method="post"
    style="display:inline;"
    onsubmit="return confirm('Refuser et supprimer cette candidature ?');"
>
    <input type="hidden" name="action" value="refuser">
    <input type="hidden" name="id" value="<?= $idCandidat ?>">

    <button type="submit" class="btn btn-danger btn-sm">
        Refuser
    </button>
</form>

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

<?php include "../includes/footer.php"; ?>