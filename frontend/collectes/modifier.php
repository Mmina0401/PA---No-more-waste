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
            "id_utilisateur" => (int) ($_POST["id_utilisateur"] ?? 0),
            "id_vehicule"    => (int) ($_POST["id_vehicule"] ?? 0),
            "date_collecte"  => $_POST["date_collecte"] ?? "",
            "heure_debut"    => $_POST["heure_debut"] ?? "",
            "heure_fin"      => $_POST["heure_fin"] ?? "",
            "adresse"        => trim($_POST["adresse"] ?? ""),
            "ville"          => trim($_POST["ville"] ?? ""),
            "code_postal"    => trim($_POST["code_postal"] ?? ""),
            "commentaire"    => trim($_POST["commentaire"] ?? ""),
            "statut"         => $_POST["statut"] ?? "EN_ATTENTE"
        ]);

        if (is_array($resultat) && isset($resultat["message"])) {
            header("Location: modifier.php?id=" . $id . "&collecte_saved=1");
            exit;
        }

        $messageErreur = "Impossible de modifier la collecte.";
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

        <h2>Modifier une collecte</h2>
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

                    <div class="mb-3">
                        <label class="form-label">ID Utilisateur</label>
                        <input
                            type="number"
                            name="id_utilisateur"
                            class="form-control"
                            value="<?= htmlspecialchars((string) ($c["id_utilisateur"] ?? "")) ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ID Véhicule</label>
                        <input
                            type="number"
                            name="id_vehicule"
                            class="form-control"
                            value="<?= htmlspecialchars((string) ($c["id_vehicule"] ?? "")) ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input
                            type="date"
                            name="date_collecte"
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
                                class="form-control"
                                value="<?= htmlspecialchars(substr($c["heure_debut"] ?? "", 0, 5)) ?>"
                                required>
                        </div>

                        <div class="col mb-3">
                            <label class="form-label">Heure fin</label>
                            <input
                                type="time"
                                name="heure_fin"
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
                            class="form-control"
                            value="<?= htmlspecialchars($c["adresse"] ?? "") ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ville</label>
                        <input
                            type="text"
                            name="ville"
                            class="form-control"
                            value="<?= htmlspecialchars($c["ville"] ?? "") ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Code postal</label>
                        <input
                            type="text"
                            name="code_postal"
                            class="form-control"
                            value="<?= htmlspecialchars($c["code_postal"] ?? "") ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <textarea
                            name="commentaire"
                            class="form-control"
                            rows="3"><?= htmlspecialchars($c["commentaire"] ?? "") ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Statut</label>

                        <select name="statut" class="form-select">
                            <?php
                            $statuts = [
                                "EN_ATTENTE" => "EN_ATTENTE",
                                "VALIDEE" => "VALIDEE",
                                "PLANIFIEE" => "PLANIFIEE",
                                "EN_COURS" => "EN_COURS",
                                "TERMINEE" => "TERMINEE",
                                "ANNULEE" => "ANNULEE"
                            ];
                            ?>

                            <?php foreach ($statuts as $valeur => $libelle): ?>
                                <option
                                    value="<?= htmlspecialchars($valeur) ?>"
                                    <?= ($c["statut"] ?? "") === $valeur ? "selected" : "" ?>
                                >
                                    <?= htmlspecialchars($libelle) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">
                        Enregistrer
                    </button>

                    <a href="index.php" class="btn btn-secondary">
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

                <?php if (($c["statut"] ?? "") !== "TERMINEE"): ?>
                    <div class="alert alert-info mb-0">
                        Passe d'abord la collecte au statut <strong>TERMINEE</strong> puis clique sur
                        <strong>Enregistrer</strong>. Tu pourras ensuite enregistrer les produits récupérés.
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
