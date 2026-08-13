<?php
session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$messageErreur = "";

$utilisateurs = API::get("/api/utilisateurs");
if (!is_array($utilisateurs)) {
    $utilisateurs = [];
}

$vehicules = API::get("/api/vehicules");
if (!is_array($vehicules)) {
    $vehicules = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idUtilisateur = (int) ($_POST["id_utilisateur"] ?? 0);
    $idVehicule = (int) ($_POST["id_vehicule"] ?? 0);
    $dateCollecte = $_POST["date_collecte"] ?? "";
    $heureDebut = $_POST["heure_debut"] ?? "";
    $heureFin = $_POST["heure_fin"] ?? "";
    $adresse = trim($_POST["adresse"] ?? "");
    $ville = trim($_POST["ville"] ?? "");
    $codePostal = trim($_POST["code_postal"] ?? "");
    $commentaire = trim($_POST["commentaire"] ?? "");
    $statut = $_POST["statut"] ?? "EN_ATTENTE";

    if ($idUtilisateur <= 0) {
        $messageErreur = "Sélectionnez un utilisateur valide.";
    } elseif ($idVehicule <= 0) {
        $messageErreur = "Sélectionnez un véhicule valide.";
    } elseif ($dateCollecte === "") {
        $messageErreur = "La date est obligatoire.";
    } elseif ($dateCollecte < date("Y-m-d")) {
        $messageErreur = "Impossible de créer une collecte dans le passé.";
    } elseif ($heureDebut === "" || $heureFin === "") {
        $messageErreur = "Les horaires sont obligatoires.";
    } elseif ($heureDebut >= $heureFin) {
        $messageErreur = "L'heure de fin doit être après l'heure de début.";
    } elseif ($adresse === "" || $ville === "" || $codePostal === "") {
        $messageErreur = "L'adresse, la ville et le code postal sont obligatoires.";
    }

    if ($messageErreur === "") {
        $resultat = API::post("/api/collectes/create", [
            "id_utilisateur" => $idUtilisateur,
            "id_vehicule" => $idVehicule,
            "date_collecte" => $dateCollecte,
            "heure_debut" => $heureDebut,
            "heure_fin" => $heureFin,
            "adresse" => $adresse,
            "ville" => $ville,
            "code_postal" => $codePostal,
            "commentaire" => $commentaire,
            "statut" => $statut
        ]);
        if (is_array($resultat) && isset($resultat["id_collecte"])) {
            header("Location: index.php?created=1");
            exit;
        }

        $messageErreur = "Impossible de créer la collecte.";
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

<h2>Ajouter une collecte</h2>
<hr>

<?php if ($messageErreur): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($messageErreur) ?>
    </div>
<?php endif; ?>

<form method="post">

<div class="mb-3">
    <label class="form-label">Utilisateur</label>

    <select
        name="id_utilisateur"
        class="form-select"
        required
    >
        <option value="">
            Sélectionner un utilisateur
        </option>

        <?php foreach ($utilisateurs as $u): ?>

            <?php
            $id = (int) ($u["id_utilisateur"] ?? 0);

            if ($id <= 0) {
                continue;
            }

            $nomComplet = trim(
                ($u["prenom"] ?? "") .
                " " .
                ($u["nom"] ?? "")
            );

            $email = $u["email"] ?? "";
            $role = $u["role"] ?? "";
            ?>

            <option
                value="<?= $id ?>"
                <?= (
                    isset($_POST["id_utilisateur"]) &&
                    (int) $_POST["id_utilisateur"] === $id
                ) ? "selected" : "" ?>
            >
                <?= htmlspecialchars($nomComplet) ?>
                <?= $email !== "" ? " - " . htmlspecialchars($email) : "" ?>
                <?= $role !== "" ? " (" . htmlspecialchars($role) . ")" : "" ?>
            </option>

        <?php endforeach; ?>

    </select>
</div>

<div class="mb-3">
    <label class="form-label">Véhicule</label>

    <select
        name="id_vehicule"
        class="form-select"
        required
    >
        <option value="">
            Sélectionner un véhicule
        </option>

        <?php foreach ($vehicules as $v): ?>

            <?php
            $idVehicule = (int) ($v["id_vehicule"] ?? 0);

            if ($idVehicule <= 0) {
                continue;
            }

            $labelVehicule = trim(
                ($v["immatriculation"] ?? "") .
                " " .
                ($v["type"] ?? "")
            );

            if ($labelVehicule === "") {
                $labelVehicule = "Véhicule #" . $idVehicule;
            }
            ?>

            <option
                value="<?= $idVehicule ?>"
                <?= (
                    isset($_POST["id_vehicule"]) &&
                    (int) $_POST["id_vehicule"] === $idVehicule
                ) ? "selected" : "" ?>
            >
                <?= htmlspecialchars($labelVehicule) ?>
            </option>

        <?php endforeach; ?>

    </select>
</div>

<div class="mb-3">
    <label class="form-label">Date</label>

    <input
        type="date"
        name="date_collecte"
        class="form-control"
        min="<?= date("Y-m-d") ?>"
        value="<?= htmlspecialchars($_POST["date_collecte"] ?? "") ?>"
        required
    >
</div>

<div class="row">

<div class="col-md-6 mb-3">
    <label class="form-label">Heure début</label>

    <input
        type="time"
        name="heure_debut"
        class="form-control"
        value="<?= htmlspecialchars($_POST["heure_debut"] ?? "08:00") ?>"
        required
    >
</div>

<div class="col-md-6 mb-3">
    <label class="form-label">Heure fin</label>

    <input
        type="time"
        name="heure_fin"
        class="form-control"
        value="<?= htmlspecialchars($_POST["heure_fin"] ?? "18:00") ?>"
        required
    >
</div>

</div>

<div class="mb-3">
    <label class="form-label">Adresse</label>

    <input
        type="text"
        name="adresse"
        class="form-control"
        value="<?= htmlspecialchars($_POST["adresse"] ?? "") ?>"
        required
    >
</div>

<div class="mb-3">
    <label class="form-label">Ville</label>

    <input
        type="text"
        name="ville"
        class="form-control"
        value="<?= htmlspecialchars($_POST["ville"] ?? "") ?>"
        required
    >
</div>

<div class="mb-3">
    <label class="form-label">Code postal</label>

    <input
        type="text"
        name="code_postal"
        class="form-control"
        value="<?= htmlspecialchars($_POST["code_postal"] ?? "") ?>"
        required
    >
</div>

<div class="mb-3">
    <label class="form-label">Commentaire</label>

    <textarea
        name="commentaire"
        class="form-control"
        rows="4"
    ><?= htmlspecialchars($_POST["commentaire"] ?? "") ?></textarea>
</div>

<div class="mb-3">
    <label class="form-label">Statut</label>

    <select
        name="statut"
        class="form-select"
    >
        <option
            value="EN_ATTENTE"
            <?= ($_POST["statut"] ?? "") === "EN_ATTENTE" ? "selected" : "" ?>
        >
            En attente
        </option>

        <option
            value="PLANIFIEE"
            <?= ($_POST["statut"] ?? "") === "PLANIFIEE" ? "selected" : "" ?>
        >
            Planifiée
        </option>

        <option
            value="TERMINEE"
            <?= ($_POST["statut"] ?? "") === "TERMINEE" ? "selected" : "" ?>
        >
            Terminée
        </option>

        <option
            value="ANNULEE"
            <?= ($_POST["statut"] ?? "") === "ANNULEE" ? "selected" : "" ?>
        >
            Annulée
        </option>
    </select>
</div>

<button
    type="submit"
    class="btn btn-success"
>
    Créer la collecte
</button>

<a
    href="index.php"
    class="btn btn-secondary"
>
    Annuler
</a>

</form>

</div>

</div>
</div>

<?php include "../includes/footer.php"; ?>