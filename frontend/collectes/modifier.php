<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$messageErreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "modifier_collecte";

    if ($action === "modifier_collecte") {
        $resultat = API::put("/api/collectes/update", [
            "id_collecte"    => $id,
            "id_vehicule"    => !empty($_POST["id_vehicule"])
                ? (int) $_POST["id_vehicule"]
                : null,
            "date_collecte"  => $_POST["date_collecte"] ?? "",
            "heure_debut"    => $_POST["heure_debut"] ?? "",
            "heure_fin"      => $_POST["heure_fin"] ?? "",
            "adresse"        => trim($_POST["adresse"] ?? ""),
            "ville"          => trim($_POST["ville"] ?? ""),
            "code_postal"    => trim($_POST["code_postal"] ?? ""),
            "commentaire"    => trim($_POST["commentaire"] ?? ""),
            "statut"         => $_POST["nouveau_statut"]
                ?? $_POST["statut_actuel"]
                ?? "EN_ATTENTE"
        ]);

        if (is_array($resultat) && isset($resultat["message"])) {
            header("Location: modifier.php?id=" . $id . "&collecte_saved=1");
            exit;
        }

        $messageErreur = "Modification refusée. Vérifie que le changement de statut suit bien l'ordre prévu.";
    }

    if ($action === "ajouter_produit") {
        $resultat = API::postAvecStatut("/api/detail_collectes/create", [
            "id_collecte" => $id,
            "id_produit" => (int) ($_POST["id_produit"] ?? 0),
            "quantite" => (float) ($_POST["quantite"] ?? 0),
            "date_dlc" => $_POST["date_dlc"] ?? "",
            "etat" => $_POST["etat"] ?? "BON",
            "observation" => trim($_POST["observation"] ?? ""),
            "emplacement" => trim($_POST["emplacement"] ?? "")
        ]);

        if (($resultat["status"] ?? 0) >= 200 && ($resultat["status"] ?? 0) < 300) {
            header("Location: modifier.php?id=" . $id . "&produit_added=1");
            exit;
        }

        $messageErreur = $resultat["data"]["message"]
            ?? "Impossible d'ajouter le produit récupéré.";
    }
}

$c = API::get("/api/collectes/get?id=" . $id);

if (!is_array($c) || empty($c["id_collecte"])) {
    header("Location: index.php");
    exit;
}

$vehicules = API::get("/api/vehicules");
if (!is_array($vehicules)) {
    $vehicules = [];
}

$statutActuel = $c["statut"] ?? "";
$estArchivee = in_array($statutActuel, ["TERMINEE", "ANNULEE"], true);

$libellesStatut = [
    "EN_ATTENTE" => "Demande en attente",
    "VALIDEE" => "Demande acceptée",
    "PLANIFIEE" => "Collecte planifiée",
    "EN_COURS" => "Collecte en cours",
    "TERMINEE" => "Collecte terminée",
    "ANNULEE" => "Collecte annulée",
];

$actionsStatut = [
    "EN_ATTENTE" => [
        "principal" => [
            "statut" => "VALIDEE",
            "libelle" => "Accepter la demande",
            "icone" => "fa-check",
        ],
        "annuler" => "Annuler la demande",
    ],
    "VALIDEE" => [
        "principal" => [
            "statut" => "PLANIFIEE",
            "libelle" => "Planifier la collecte",
            "icone" => "fa-calendar-check",
        ],
        "annuler" => "Annuler la collecte",
    ],
    "PLANIFIEE" => [
        "principal" => [
            "statut" => "EN_COURS",
            "libelle" => "Démarrer la collecte",
            "icone" => "fa-play",
        ],
        "annuler" => "Annuler la collecte",
    ],
    "EN_COURS" => [
        "principal" => [
            "statut" => "TERMINEE",
            "libelle" => "Terminer la collecte",
            "icone" => "fa-flag-checkered",
        ],
        "annuler" => "Annuler la collecte",
    ],
];

$libelleStatutActuel = $libellesStatut[$statutActuel]
    ?? ucfirst(strtolower(str_replace("_", " ", $statutActuel)));

$actionStatut = $actionsStatut[$statutActuel] ?? null;

$produits = API::get("/api/produits");
if (!is_array($produits)) {
    $produits = [];
}

$produits = array_values(array_filter($produits, function ($produit) {
    return !empty($produit["actif"]);
}));

$detailsCollecte = API::get("/api/detail_collectes?id_collecte=" . $id);
if (!is_array($detailsCollecte)) {
    $detailsCollecte = [];
}

$stocks = API::get("/api/stocks");
if (!is_array($stocks)) {
    $stocks = [];
}

$stocksParProduit = [];
foreach ($stocks as $stock) {
    $idProduitStock = (int) ($stock["id_produit"] ?? 0);
    if ($idProduitStock > 0) {
        $stocksParProduit[$idProduitStock] = $stock;
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

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="mb-1"><?=
                    $estArchivee
                        ? "Consulter une collecte archivée"
                        : ($statutActuel === "EN_ATTENTE"
                            ? "Gérer la demande de collecte"
                            : "Gérer la collecte")
                ?></h2>
                <?php if ($estArchivee): ?>
                    <p class="text-muted mb-0">
                        Le statut et les informations principales sont verrouillés pour conserver l'historique.
                    </p>
                <?php endif; ?>
            </div>

            <?php if ($estArchivee): ?>
                <a href="historique.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i>
                    Retour à l'historique
                </a>
            <?php endif; ?>
        </div>
        <hr>

        <?php if (isset($_GET["collecte_saved"]) && $_GET["collecte_saved"] === "1"): ?>
            <div class="alert alert-success">
                La collecte a été modifiée avec succès.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["produit_added"]) && $_GET["produit_added"] === "1"): ?>
            <div class="alert alert-success">
                Le produit a été ajouté à la collecte, au stock et aux mouvements de stock.
            </div>
        <?php endif; ?>

        <?php if ($messageErreur !== ""): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($messageErreur) ?>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header fw-bold">
                Informations de la collecte
            </div>

            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="modifier_collecte">
                    <input type="hidden" name="statut_actuel" value="<?= htmlspecialchars($statutActuel) ?>">

                    <div class="card border-0 bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="fa-solid fa-user me-1"></i>
                                Demandeur
                            </h5>

                            <?php
                            $nomDemandeur = trim(
                                ($c["demandeur_prenom"] ?? "") . " " .
                                ($c["demandeur_nom"] ?? "")
                            );
                            $raisonSociale = trim((string) ($c["demandeur_raison_sociale"] ?? ""));
                            $roleDemandeur = $c["demandeur_role"] ?? "";
                            ?>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="small text-muted">Nom / entreprise</div>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $raisonSociale !== ""
                                                ? $raisonSociale
                                                : ($nomDemandeur ?: "—")
                                        ) ?>
                                    </strong>
                                    <?php if ($raisonSociale !== "" && $nomDemandeur !== ""): ?>
                                        <div class="small"><?= htmlspecialchars($nomDemandeur) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-3">
                                    <div class="small text-muted">Type</div>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $roleDemandeur === "COMMERCANT"
                                                ? "Commerçant"
                                                : "Particulier"
                                        ) ?>
                                    </strong>
                                </div>

                                <div class="col-md-3">
                                    <div class="small text-muted">Email</div>
                                    <strong><?= htmlspecialchars($c["demandeur_email"] ?? "—") ?></strong>
                                </div>

                                <div class="col-md-2">
                                    <div class="small text-muted">Téléphone</div>
                                    <strong><?= htmlspecialchars($c["demandeur_telephone"] ?? "—") ?></strong>
                                </div>
                            </div>

                            <div class="form-text mt-2">
                                Le demandeur est lié à la demande et ne peut pas être changé par l'administrateur.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Véhicule</label>

                        <?php if ($estArchivee): ?>
                            <input
                                type="text"
                                class="form-control"
                                value="<?= htmlspecialchars(
                                    trim(
                                        ($c["vehicule_immatriculation"] ?? "") . " — " .
                                        ($c["vehicule_marque"] ?? "") . " " .
                                        ($c["vehicule_modele"] ?? "")
                                    ) ?: "Non attribué"
                                ) ?>"
                                disabled>
                        <?php else: ?>
                            <select name="id_vehicule" class="form-select">
                                <option value="">Non attribué pour le moment</option>

                                <?php foreach ($vehicules as $vehicule): ?>
                                    <?php
                                    $idVehicule = (int) ($vehicule["id_vehicule"] ?? 0);
                                    if ($idVehicule <= 0) {
                                        continue;
                                    }

                                    $libelleVehicule = trim(
                                        ($vehicule["immatriculation"] ?? ("Véhicule #" . $idVehicule)) .
                                        " — " .
                                        ($vehicule["marque"] ?? "") . " " .
                                        ($vehicule["modele"] ?? "")
                                    );

                                    $etatVehicule = trim((string) ($vehicule["etat"] ?? ""));
                                    if ($etatVehicule !== "") {
                                        $libelleVehicule .= " (" . $etatVehicule . ")";
                                    }
                                    ?>
                                    <option
                                        value="<?= $idVehicule ?>"
                                        <?= (int) ($c["id_vehicule"] ?? 0) === $idVehicule ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($libelleVehicule) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="form-text">
                                Un véhicule est obligatoire avant de passer la collecte au statut Planifiée.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input
                            type="date"
                            name="date_collecte"
                            <?= $estArchivee ? "disabled" : "" ?>
                            class="form-control"
                            value="<?= htmlspecialchars(substr($c["date_collecte"] ?? "", 0, 10)) ?>"
                            required>
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Heure début</label>
                            <input
                                type="time"
                                name="heure_debut"
                            <?= $estArchivee ? "disabled" : "" ?>
                                class="form-control"
                                value="<?= htmlspecialchars(substr($c["heure_debut"] ?? "", 0, 5)) ?>"
                                required>
                        </div>

                        <div class="col mb-3">
                            <label class="form-label">Heure fin</label>
                            <input
                                type="time"
                                name="heure_fin"
                            <?= $estArchivee ? "disabled" : "" ?>
                                class="form-control"
                                value="<?= htmlspecialchars(substr($c["heure_fin"] ?? "", 0, 5)) ?>"
                                required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adresse</label>
                        <input
                            type="text"
                            name="adresse"
                            <?= $estArchivee ? "disabled" : "" ?>
                            class="form-control"
                            value="<?= htmlspecialchars($c["adresse"] ?? "") ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ville</label>
                        <input
                            type="text"
                            name="ville"
                            <?= $estArchivee ? "disabled" : "" ?>
                            class="form-control"
                            value="<?= htmlspecialchars($c["ville"] ?? "") ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Code postal</label>
                        <input
                            type="text"
                            name="code_postal"
                            <?= $estArchivee ? "disabled" : "" ?>
                            class="form-control"
                            value="<?= htmlspecialchars($c["code_postal"] ?? "") ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <textarea
                            name="commentaire"
                            <?= $estArchivee ? "disabled" : "" ?>
                            class="form-control"
                            rows="3"><?= htmlspecialchars($c["commentaire"] ?? "") ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Avancement de la collecte</label>

                        <div class="border rounded p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <div class="small text-muted mb-1">Statut actuel</div>
                                    <strong><?= htmlspecialchars($libelleStatutActuel) ?></strong>
                                </div>

                                <?php if (!$estArchivee && $actionStatut): ?>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button
                                            type="submit"
                                            name="nouveau_statut"
                                            value="<?= htmlspecialchars($actionStatut["principal"]["statut"]) ?>"
                                            class="btn btn-success"
                                        >
                                            <i class="fa-solid <?= htmlspecialchars($actionStatut["principal"]["icone"]) ?> me-1"></i>
                                            <?= htmlspecialchars($actionStatut["principal"]["libelle"]) ?>
                                        </button>

                                        <button
                                            type="submit"
                                            name="nouveau_statut"
                                            value="ANNULEE"
                                            class="btn btn-outline-danger"
                                            onclick="return confirm('Confirmer l\'annulation de cette collecte ?');"
                                        >
                                            <i class="fa-solid fa-xmark me-1"></i>
                                            <?= htmlspecialchars($actionStatut["annuler"]) ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!$estArchivee): ?>
                                <div class="form-text mt-2">
                                    Le parcours suit automatiquement l'ordre :
                                    demande acceptée → collecte planifiée → collecte en cours → collecte terminée.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!$estArchivee): ?>
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Enregistrer les informations
                        </button>
                    <?php endif; ?>

                    <a href="<?= $estArchivee ? "historique.php" : "index.php" ?>" class="btn btn-secondary">
                        Retour
                    </a>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header fw-bold">
                Produits récupérés
            </div>

            <div class="card-body">

                <?php if (($c["statut"] ?? "") === "ANNULEE"): ?>
                    <div class="alert alert-secondary mb-0">
                        Cette collecte a été annulée. Aucun produit récupéré ne peut être ajouté.
                    </div>

                <?php elseif (($c["statut"] ?? "") !== "TERMINEE"): ?>
                    <div class="alert alert-info mb-0">
                        Termine d'abord le parcours de la collecte avec le bouton
                        <strong>Terminer la collecte</strong>. Tu pourras ensuite enregistrer les produits réellement récupérés.
                    </div>
                <?php else: ?>

                    <?php if (empty($produits)): ?>
                        <div class="alert alert-warning">
                            Aucun produit actif n'est disponible dans le catalogue.
                        </div>
                    <?php else: ?>
                        <form method="post" class="mb-4">
                            <input type="hidden" name="action" value="ajouter_produit">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Produit</label>
                                    <select name="id_produit" class="form-select" required>
                                        <option value="">Sélectionner un produit</option>

                                        <?php foreach ($produits as $produit): ?>
                                            <?php
                                            $idProduit = (int) ($produit["id_produit"] ?? 0);
                                            $stockProduit = $stocksParProduit[$idProduit] ?? null;
                                            ?>
                                            <option
                                                value="<?= $idProduit ?>"
                                                <?= isset($_POST["id_produit"]) && (int) $_POST["id_produit"] === $idProduit ? "selected" : "" ?>
                                            >
                                                <?= htmlspecialchars($produit["nom"] ?? "Produit") ?>
                                                <?php if (!empty($produit["code_barre"])): ?>
                                                    - <?= htmlspecialchars($produit["code_barre"]) ?>
                                                <?php endif; ?>
                                                <?php if ($stockProduit): ?>
                                                    (stock : <?= htmlspecialchars((string) ($stockProduit["quantite"] ?? 0)) ?>,
                                                    <?= htmlspecialchars($stockProduit["emplacement"] ?? "") ?>)
                                                <?php else: ?>
                                                    (pas encore en stock)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Quantité récupérée</label>
                                    <input
                                        type="number"
                                        name="quantite"
                                        class="form-control"
                                        min="0.01"
                                        step="0.01"
                                        value="<?= htmlspecialchars($_POST["quantite"] ?? "") ?>"
                                        required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">DLC</label>
                                    <input
                                        type="date"
                                        name="date_dlc"
                                        class="form-control"
                                        value="<?= htmlspecialchars($_POST["date_dlc"] ?? "") ?>"
                                        required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">État</label>
                                    <select name="etat" class="form-select" required>
                                        <?php
                                        $etatSelectionne = $_POST["etat"] ?? "BON";
                                        foreach (["EXCELLENT", "BON", "MOYEN", "ABIME"] as $etat):
                                        ?>
                                            <option value="<?= $etat ?>" <?= $etatSelectionne === $etat ? "selected" : "" ?>>
                                                <?= $etat ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Emplacement</label>
                                    <input
                                        type="text"
                                        name="emplacement"
                                        class="form-control"
                                        value="<?= htmlspecialchars($_POST["emplacement"] ?? "") ?>"
                                        placeholder="Ex. A1-04">
                                    <div class="form-text">
                                        Obligatoire seulement si ce produit n'a pas encore de stock.
                                        S'il existe déjà, son emplacement actuel est conservé.
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Observation</label>
                                    <input
                                        type="text"
                                        name="observation"
                                        class="form-control"
                                        value="<?= htmlspecialchars($_POST["observation"] ?? "") ?>"
                                        placeholder="Optionnel">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Ajouter le produit récupéré
                            </button>
                        </form>
                    <?php endif; ?>

                    <h5>Produits déjà enregistrés pour cette collecte</h5>

                    <?php if (empty($detailsCollecte)): ?>
                        <p class="text-muted mb-0">
                            Aucun produit récupéré n'a encore été enregistré.
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>DLC</th>
                                        <th>État</th>
                                        <th>Observation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detailsCollecte as $detail): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($detail["nom_produit"] ?? ("Produit #" . ($detail["id_produit"] ?? ""))) ?></td>
                                            <td><?= htmlspecialchars((string) ($detail["quantite"] ?? "")) ?></td>
                                            <td>
                                                <?php
                                                $dateDlc = substr($detail["date_dlc"] ?? "", 0, 10);
                                                $timestampDlc = $dateDlc !== "" ? strtotime($dateDlc) : false;
                                                echo htmlspecialchars($timestampDlc ? date("d/m/Y", $timestampDlc) : ($dateDlc ?: "—"));
                                                ?>
                                            </td>
                                            <td><?= htmlspecialchars($detail["etat"] ?? "—") ?></td>
                                            <td><?= htmlspecialchars($detail["observation"] ?? "—") ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
</div>

<?php include "../includes/footer.php"; ?>
