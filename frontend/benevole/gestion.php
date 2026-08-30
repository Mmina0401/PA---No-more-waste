<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$messageErreur = null;

// Actions : modification ou suppression d'un bénévole
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $idUtilisateur = (int) ($_POST["id_utilisateur"] ?? 0);

    if ($idUtilisateur <= 0) {
        $messageErreur = "Bénévole invalide.";
    } elseif ($action === "modifier") {
        $competencesSelectionnees = array_values(array_unique(array_map(
            "intval",
            $_POST["competences"] ?? []
        )));

        $reponse = API::post("/api/benevoles/update", [
            "id_utilisateur" => $idUtilisateur,
            "nom"            => trim($_POST["nom"] ?? ""),
            "prenom"         => trim($_POST["prenom"] ?? ""),
            "email"          => trim($_POST["email"] ?? ""),
            "telephone"      => trim($_POST["telephone"] ?? ""),
            "ville"          => trim($_POST["ville"] ?? ""),
            "actif"          => ($_POST["actif"] ?? "0") === "1",
            "competences"    => $competencesSelectionnees,
        ]);

        if (is_array($reponse) && isset($reponse["message"])) {
            header("Location: gestion.php?succes=modification");
            exit;
        }

        $messageErreur = "Impossible de modifier le bénévole.";
    } elseif ($action === "supprimer") {
        $reponse = API::post("/api/benevoles/delete", [
            "id_utilisateur" => $idUtilisateur,
        ]);

        if (is_array($reponse) && isset($reponse["message"])) {
            header("Location: gestion.php?succes=suppression");
            exit;
        }

        $messageErreur = "Impossible de supprimer le bénévole.";
    }
}

$benevoles = API::get("/api/benevoles");
if (!is_array($benevoles)) {
    $benevoles = [];
}

// Catalogue des compétences : utilisé uniquement pour le formulaire de modification.
$catalogueCompetences = API::get("/api/public/competences");
if (!is_array($catalogueCompetences)) {
    $catalogueCompetences = [];
}

$competenceIdParNom = [];
foreach ($catalogueCompetences as $competence) {
    if (is_array($competence) && isset($competence["id_competence"], $competence["nom"])) {
        $competenceIdParNom[$competence["nom"]] = (int) $competence["id_competence"];
    }
}

$nbActifs = 0;
$nbEnAttente = 0;

foreach ($benevoles as $benevole) {
    if (!is_array($benevole)) {
        continue;
    }

    if (!empty($benevole["actif"])) {
        $nbActifs++;
    } else {
        $nbEnAttente++;
    }
}

$benevoleAModifier = null;
$idAModifier = (int) ($_GET["modifier"] ?? 0);

if ($idAModifier > 0) {
    foreach ($benevoles as $benevole) {
        if ((int) ($benevole["id_utilisateur"] ?? 0) === $idAModifier) {
            $benevoleAModifier = $benevole;
            break;
        }
    }
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

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">Gestion des bénévoles</h2>
            <p class="text-muted mb-0">
                Consultez et gérez les bénévoles enregistrés, leur statut et leurs compétences.
            </p>
        </div>

        <a href="../candidatures/index.php" class="btn btn-success">
            <i class="fa-solid fa-user-plus me-1"></i>
            Voir les candidatures
        </a>
    </div>

    <?php if (($_GET["succes"] ?? "") === "modification"): ?>
        <div class="alert alert-success">Le bénévole a été modifié avec succès.</div>
    <?php elseif (($_GET["succes"] ?? "") === "suppression"): ?>
        <div class="alert alert-success">Le bénévole a été supprimé avec succès.</div>
    <?php endif; ?>

    <?php if ($messageErreur): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($messageErreur, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Bénévoles enregistrés</div>
                    <div class="fs-3 fw-bold"><?= count($benevoles) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Comptes validés</div>
                    <div class="fs-3 fw-bold text-success"><?= $nbActifs ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">En attente</div>
                    <div class="fs-3 fw-bold text-warning"><?= $nbEnAttente ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($benevoleAModifier): ?>
        <?php
        $competencesActuelles = $benevoleAModifier["competences"] ?? [];
        if (!is_array($competencesActuelles)) {
            $competencesActuelles = [];
        }
        ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header fw-bold">Modifier le bénévole</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="modifier">
                    <input type="hidden" name="id_utilisateur" value="<?= (int) $benevoleAModifier["id_utilisateur"] ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="prenom" class="form-control"
                                   value="<?= htmlspecialchars($benevoleAModifier["prenom"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="nom" class="form-control"
                                   value="<?= htmlspecialchars($benevoleAModifier["nom"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($benevoleAModifier["email"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control"
                                   value="<?= htmlspecialchars($benevoleAModifier["telephone"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ville</label>
                            <input type="text" name="ville" class="form-control"
                                   value="<?= htmlspecialchars($benevoleAModifier["ville"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Statut du compte</label>
                        <select name="actif" class="form-select">
                            <option value="1" <?= !empty($benevoleAModifier["actif"]) ? "selected" : "" ?>>Validé</option>
                            <option value="0" <?= empty($benevoleAModifier["actif"]) ? "selected" : "" ?>>En attente / désactivé</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Compétences</label>

                        <?php if (empty($catalogueCompetences)): ?>
                            <div class="alert alert-warning mb-0">Aucune compétence disponible.</div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($catalogueCompetences as $competence): ?>
                                    <?php
                                    $idCompetence = (int) ($competence["id_competence"] ?? 0);
                                    $nomCompetence = $competence["nom"] ?? "";
                                    $estSelectionnee = in_array($nomCompetence, $competencesActuelles, true);
                                    ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="competences[]"
                                                value="<?= $idCompetence ?>"
                                                id="competence-<?= $idCompetence ?>"
                                                <?= $estSelectionnee ? "checked" : "" ?>
                                            >
                                            <label class="form-check-label" for="competence-<?= $idCompetence ?>">
                                                <?= htmlspecialchars($nomCompetence, ENT_QUOTES, "UTF-8") ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
                    <a href="gestion.php" class="btn btn-secondary">Annuler</a>
                </form>
            </div>
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
                        <th>Bénévole</th>
                        <th>Contact</th>
                        <th>Ville</th>
                        <th>Compétences</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($benevoles as $benevole): ?>
                    <?php
                    if (!is_array($benevole)) {
                        continue;
                    }

                    $competences = $benevole["competences"] ?? [];
                    if (!is_array($competences)) {
                        $competences = [];
                    }
                    ?>

                    <tr>
                        <td>
                            <strong>
                                <?= htmlspecialchars(
                                    trim(
                                        ($benevole["prenom"] ?? "") . " " .
                                        ($benevole["nom"] ?? "")
                                    ),
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </strong>
                        </td>

                        <td>
                            <div><?= htmlspecialchars($benevole["email"] ?? "—", ENT_QUOTES, "UTF-8") ?></div>
                            <small class="text-muted">
                                <?= htmlspecialchars($benevole["telephone"] ?? "—", ENT_QUOTES, "UTF-8") ?>
                            </small>
                        </td>

                        <td>
                            <?= htmlspecialchars($benevole["ville"] ?? "—", ENT_QUOTES, "UTF-8") ?>
                        </td>

                        <td>
                            <?php if (empty($competences)): ?>
                                <span class="text-muted">Aucune compétence renseignée</span>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($competences as $competence): ?>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($competence, ENT_QUOTES, "UTF-8") ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if (!empty($benevole["actif"])): ?>
                                <span class="badge bg-success">Validé</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">En attente</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-nowrap">
                            <a
                                href="gestion.php?modifier=<?= (int) $benevole["id_utilisateur"] ?>"
                                class="btn btn-warning btn-sm"
                            >
                                Modifier
                            </a>

                            <form
                                method="post"
                                class="d-inline"
                                onsubmit="return confirm('Supprimer définitivement ce bénévole ?');"
                            >
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="id_utilisateur" value="<?= (int) $benevole["id_utilisateur"] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Supprimer
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
