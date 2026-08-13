<?php
session_start();
require_once __DIR__ . "/../includes/api.php";

$messageSucces = null;
$messageErreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $typeDemandeur = $_POST["type_demandeur"] ?? "particulier";

    $reponse = API::post("/api/public/demande-collecte", [
        "nom"              => trim($_POST["nom"]),
        "prenom"           => trim($_POST["prenom"]),
        "email"            => trim($_POST["email"]),
        "adresse"          => trim($_POST["adresse"]),
        "ville"            => trim($_POST["ville"]),
        "code_postal"      => trim($_POST["code_postal"]),
        "date_collecte"    => $_POST["date_collecte"],
        "heure_debut"      => $_POST["heure_debut"],
        "heure_fin"        => $_POST["heure_fin"],
        "commentaire"      => trim($_POST["commentaire"]),
        "raison_sociale"   => $typeDemandeur === "commercant" ? trim($_POST["raison_sociale"]) : null,
        "siret"            => $typeDemandeur === "commercant" ? trim($_POST["siret"]) : null,
        "secteur_activite" => $typeDemandeur === "commercant" ? trim($_POST["secteur_activite"]) : null,
    ]);

    if (isset($reponse["message"])) {
        $messageSucces = "Votre demande a bien été enregistrée. Un responsable va la valider et vous recontacter.";
    } else {
        $messageErreur = "Impossible d'enregistrer votre demande, vérifiez les champs et réessayez.";
    }
}

include __DIR__ . "/entete.php";
?>

<div class="container py-5" style="max-width: 640px;">

<h1 class="mb-3">Demander une collecte</h1>
<p class="texte-secondaire mb-4">Un commerçant ou un particulier peut demander le passage d'un camion, sans créer de compte.</p>

<?php if ($messageSucces): ?>
    <div class="alert alert-success"><?= htmlspecialchars($messageSucces) ?></div>
<?php elseif ($messageErreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($messageErreur) ?></div>
<?php endif; ?>

<?php if (!$messageSucces): ?>
<div class="carte-nmw p-4">
<form method="post">

    <div class="mb-4">
        <label class="form-label d-block mb-2">Je suis...</label>
        <div class="btn-group w-100" role="group">
            <input type="radio" class="btn-check" name="type_demandeur" id="type_particulier" value="particulier" checked onclick="basculerChampsPro(false)">
            <label class="btn btn-outline-secondary" for="type_particulier">Un particulier</label>

            <input type="radio" class="btn-check" name="type_demandeur" id="type_commercant" value="commercant" onclick="basculerChampsPro(true)">
            <label class="btn btn-outline-secondary" for="type_commercant">Un commerçant</label>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Prénom</label>
            <input type="text" name="prenom" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
    </div>

    <div id="champsPro" style="display:none;">
        <div class="mb-3">
            <label class="form-label">Raison sociale</label>
            <input type="text" name="raison_sociale" class="form-control">
        </div>
        <div class="row">
            <div class="col-md-7 mb-3">
                <label class="form-label">SIRET</label>
                <input type="text" name="siret" class="form-control" maxlength="14">
            </div>
            <div class="col-md-5 mb-3">
                <label class="form-label">Secteur d'activité</label>
                <input type="text" name="secteur_activite" class="form-control">
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Adresse de collecte</label>
        <input type="text" name="adresse" class="form-control" required>
    </div>

    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label">Ville</label>
            <input type="text" name="ville" class="form-control" required>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Code postal</label>
            <input type="text" name="code_postal" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Date souhaitée</label>
        <input type="date" name="date_collecte" class="form-control" required>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Créneau à partir de</label>
            <input type="time" name="heure_debut" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Jusqu'à</label>
            <input type="time" name="heure_fin" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Commentaire (optionnel)</label>
        <textarea name="commentaire" class="form-control" rows="3"></textarea>
    </div>

    <button type="submit" class="btn btn-secondaire w-100">Envoyer la demande</button>
</form>
</div>
<?php endif; ?>

</div>

<script>
function basculerChampsPro(afficher) {
    document.getElementById("champsPro").style.display = afficher ? "block" : "none";
}
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>