<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$benevoles = API::get("/api/benevoles");

if (!is_array($benevoles)) {
    $benevoles = [];
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
                Consultez les bénévoles enregistrés, leur statut et leurs compétences.
            </p>
        </div>

        <a href="../candidatures/index.php" class="btn btn-success">
            <i class="fa-solid fa-user-plus me-1"></i>
            Voir les candidatures
        </a>
    </div>

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
                                    )
                                ) ?>
                            </strong>
                        </td>

                        <td>
                            <div><?= htmlspecialchars($benevole["email"] ?? "—") ?></div>
                            <small class="text-muted">
                                <?= htmlspecialchars($benevole["telephone"] ?? "—") ?>
                            </small>
                        </td>

                        <td>
                            <?= htmlspecialchars($benevole["ville"] ?? "—") ?>
                        </td>

                        <td>
                            <?php if (empty($competences)): ?>
                                <span class="text-muted">Aucune compétence renseignée</span>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($competences as $competence): ?>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($competence) ?>
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
