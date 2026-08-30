<?php
session_start();

require_once __DIR__ . "/../includes/api.php";

$messageSucces = null;
$messageErreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $appel = API::postAvecStatut("/api/public/devenir-adherent", [
        "nom"              => trim($_POST["nom"] ?? ""),
        "prenom"           => trim($_POST["prenom"] ?? ""),
        "email"            => trim($_POST["email"] ?? ""),
        "mot_de_passe"     => $_POST["mot_de_passe"] ?? "",
        "telephone"        => trim($_POST["telephone"] ?? ""),
        "adresse"          => trim($_POST["adresse"] ?? ""),
        "ville"            => trim($_POST["ville"] ?? ""),
        "code_postal"      => trim($_POST["code_postal"] ?? ""),
        "raison_sociale"   => trim($_POST["raison_sociale"] ?? ""),
        "siret"            => trim($_POST["siret"] ?? ""),
        "secteur_activite" => trim($_POST["secteur_activite"] ?? ""),
    ]);

    $reponse = $appel["data"] ?? [];

    if (($appel["status"] ?? 0) >= 200 && ($appel["status"] ?? 0) < 300) {
        $messageSucces = $reponse["message"] ?? "Votre demande d'adhésion a bien été enregistrée.";
    } else {
        $messageErreur = $reponse["error"] ?? "Impossible d'enregistrer votre demande.";
    }
}

include __DIR__ . "/entete.php";
?>

<div class="container py-5" style="max-width: 760px;">

    <div class="text-center mb-4">
        <span class="badge badge-jaune mb-3 px-3 py-2">Adhésion commerçant</span>
        <h1>Devenir adhérent</h1>
        <p class="texte-secondaire fs-6">
            L'adhésion est destinée aux commerçants. Une fois votre compte validé
            et votre cotisation active, vous pourrez vous inscrire aux services No More Waste.
        </p>
    </div>

    <?php if ($messageSucces): ?>
        <div class="alert alert-success"><?= htmlspecialchars($messageSucces) ?></div>
        <div class="text-center">
            <a href="/login.php" class="btn btn-principal">Aller à la connexion</a>
        </div>
    <?php else: ?>

        <?php if ($messageErreur): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($messageErreur) ?></div>
        <?php endif; ?>

        <div class="carte-nmw p-4 p-md-5">
            <form method="post">

                <h4 class="mb-3">Entreprise</h4>

                <div class="mb-3">
                    <label class="form-label">Raison sociale</label>
                    <input type="text" name="raison_sociale" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SIRET</label>
                        <input type="text" name="siret" class="form-control"
                               maxlength="14" pattern="[0-9]{14}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Secteur d'activité</label>
                        <input type="text" name="secteur_activite" class="form-control">
                    </div>
                </div>

                <h4 class="mt-3 mb-3">Contact</h4>

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

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="adresse" class="form-control">
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Code postal</label>
                        <input type="text" name="code_postal" class="form-control">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="mot_de_passe" class="form-control"
                           minlength="8" required>
                    <div class="form-text">8 caractères minimum.</div>
                </div>

                <button type="submit" class="btn btn-principal w-100">
                    Envoyer ma demande d'adhésion
                </button>

            </form>
        </div>

    <?php endif; ?>

</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
