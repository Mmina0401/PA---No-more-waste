<?php
session_start();

require_once "../includes/api.php";

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    API::put("/api/collectes/update", [

        "id_collecte"    => $id,
        "id_utilisateur" => (int)$_POST["id_utilisateur"],
        "id_vehicule"    => (int)$_POST["id_vehicule"],
        "date_collecte"  => $_POST["date_collecte"],
        "heure_debut"    => $_POST["heure_debut"],
        "heure_fin"      => $_POST["heure_fin"],
        "adresse"        => trim($_POST["adresse"]),
        "ville"          => trim($_POST["ville"]),
        "code_postal"    => trim($_POST["code_postal"]),
        "commentaire"    => trim($_POST["commentaire"]),
        "statut"         => $_POST["statut"]

    ]);

    echo "<script>window.location='index.php';</script>";
    exit;
}

$c = API::get("/api/collectes/get?id=".$id);

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

        <form method="post">

            <div class="mb-3">
                <label class="form-label">ID Utilisateur</label>
                <input
                    type="number"
                    name="id_utilisateur"
                    class="form-control"
                    value="<?= htmlspecialchars($c["id_utilisateur"]) ?>"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">ID Véhicule</label>
                <input
                    type="number"
                    name="id_vehicule"
                    class="form-control"
                    value="<?= htmlspecialchars($c["id_vehicule"]) ?>"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Date</label>
                <input
                    type="date"
                    name="date_collecte"
                    class="form-control"
                    value="<?= htmlspecialchars(substr($c["date_collecte"],0,10)) ?>"
                    required>
            </div>

            <div class="row">

                <div class="col mb-3">
                    <label class="form-label">Heure début</label>
                    <input
                        type="time"
                        name="heure_debut"
                        class="form-control"
                        value="<?= htmlspecialchars(substr($c["heure_debut"],0,5)) ?>"
                        required>
                </div>

                <div class="col mb-3">
                    <label class="form-label">Heure fin</label>
                    <input
                        type="time"
                        name="heure_fin"
                        class="form-control"
                        value="<?= htmlspecialchars(substr($c["heure_fin"],0,5)) ?>"
                        required>
                </div>

            </div>

            <div class="mb-3">
                <label class="form-label">Adresse</label>
                <input
                    type="text"
                    name="adresse"
                    class="form-control"
                    value="<?= htmlspecialchars($c["adresse"]) ?>"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Ville</label>
                <input
                    type="text"
                    name="ville"
                    class="form-control"
                    value="<?= htmlspecialchars($c["ville"]) ?>"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Code postal</label>
                <input
                    type="text"
                    name="code_postal"
                    class="form-control"
                    value="<?= htmlspecialchars($c["code_postal"]) ?>"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Commentaire</label>
                <textarea
                    name="commentaire"
                    class="form-control"
                    rows="3"><?= htmlspecialchars($c["commentaire"]) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Statut</label>

                <select
                    name="statut"
                    class="form-select">

                    <option value="EN_ATTENTE" <?= $c["statut"]=="EN_ATTENTE" ? "selected" : "" ?>>
                        EN_ATTENTE
                    </option>

                    <option value="PLANIFIEE" <?= $c["statut"]=="PLANIFIEE" ? "selected" : "" ?>>
                        PLANIFIEE
                    </option>

                    <option value="TERMINEE" <?= $c["statut"]=="TERMINEE" ? "selected" : "" ?>>
                        TERMINEE
                    </option>

                    <option value="ANNULEE" <?= $c["statut"]=="ANNULEE" ? "selected" : "" ?>>
                        ANNULEE
                    </option>

                </select>

            </div>

            <button type="submit" class="btn btn-success">
                Enregistrer
            </button>

            <a href="index.php" class="btn btn-secondary">
                Annuler
            </a>

        </form>

    </div>

</div>

</div>

<?php include "../includes/footer.php"; ?>