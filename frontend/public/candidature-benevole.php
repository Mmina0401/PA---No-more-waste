<?php
// PUBLIC — Formulaire de candidature bénévole

session_start();

require_once __DIR__ . "/../includes/api.php";
require_once __DIR__ . "/../includes/lang.php";

$messageSucces = null;
$messageErreur = null;

$competencesDisponibles = API::get("/api/public/competences");

if (!is_array($competencesDisponibles)) {
    $competencesDisponibles = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $competencesSelectionnees = array_values(
        array_filter(
            array_map("intval", $_POST["competences"] ?? []),
            fn($id) => $id > 0
        )
    );

    if (empty($competencesSelectionnees)) {
        $messageErreur = $langue === "en"
            ? "Please select at least one skill."
            : "Veuillez sélectionner au moins une compétence.";
    } else {
        $reponse = API::post("/api/public/candidature-benevole", [
            "nom"          => trim($_POST["nom"] ?? ""),
            "prenom"       => trim($_POST["prenom"] ?? ""),
            "email"        => trim($_POST["email"] ?? ""),
            "mot_de_passe" => $_POST["mot_de_passe"] ?? "",
            "telephone"    => trim($_POST["telephone"] ?? ""),
            "ville"        => trim($_POST["ville"] ?? ""),
            "competences"  => $competencesSelectionnees,
        ]);

        if (isset($reponse["message"])) {
            $messageSucces = t("volunteer_success");
        } else {
            $messageErreur = t("volunteer_error");
        }
    }
}

include __DIR__ . "/entete.php";
?>

<div class="container py-5" style="max-width: 620px;">

<h1 class="mb-3"><?= t("volunteer_title") ?></h1>
<p class="texte-secondaire mb-4"><?= t("volunteer_intro") ?></p>

<?php if ($messageSucces): ?>
    <div class="alert alert-success"><?= htmlspecialchars($messageSucces, ENT_QUOTES, "UTF-8") ?></div>
<?php elseif ($messageErreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($messageErreur, ENT_QUOTES, "UTF-8") ?></div>
<?php endif; ?>

<?php if (!$messageSucces): ?>
<div class="carte-nmw p-4">

<form method="post" id="formCandidature">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label"><?= t("first_name") ?></label>
            <input
                type="text"
                name="prenom"
                class="form-control"
                value="<?= htmlspecialchars($_POST["prenom"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                required
            >
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label"><?= t("last_name") ?></label>
            <input
                type="text"
                name="nom"
                class="form-control"
                value="<?= htmlspecialchars($_POST["nom"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                required
            >
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("email") ?></label>
        <input
            type="email"
            name="email"
            class="form-control"
            value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>"
            required
        >
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("choose_password") ?></label>
        <input type="password" name="mot_de_passe" class="form-control" required>
        <div class="form-text"><?= t("password_help") ?></div>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("phone") ?></label>
        <input
            type="text"
            name="telephone"
            class="form-control"
            value="<?= htmlspecialchars($_POST["telephone"] ?? "", ENT_QUOTES, "UTF-8") ?>"
        >
    </div>

    <div class="mb-3">
        <label class="form-label"><?= t("city") ?></label>
        <input
            type="text"
            name="ville"
            class="form-control"
            value="<?= htmlspecialchars($_POST["ville"] ?? "", ENT_QUOTES, "UTF-8") ?>"
        >
    </div>

    <div class="mb-4">
        <label class="form-label fw-bold">
            <?= $langue === "en" ? "Skills" : "Compétences" ?>
        </label>

        <div class="form-text mb-2">
            <?= $langue === "en"
                ? "Select at least one skill that you can offer to the association."
                : "Sélectionnez au moins une compétence que vous pouvez proposer à l'association." ?>
        </div>

        <?php if (empty($competencesDisponibles)): ?>
            <div class="alert alert-warning mb-0">
                <?= $langue === "en"
                    ? "No skills are currently available."
                    : "Aucune compétence n'est disponible pour le moment." ?>
            </div>
        <?php else: ?>

            <div class="row">

                <?php
                $selectionnees = array_map("intval", $_POST["competences"] ?? []);
                ?>

                <?php foreach ($competencesDisponibles as $competence): ?>

                    <?php
                    $idCompetence = (int) ($competence["id_competence"] ?? 0);
                    $nomCompetence = $competence["nom"] ?? "";
                    ?>

                    <div class="col-md-6 mb-2">
                        <div class="form-check">

                            <input
                                class="form-check-input competence-checkbox"
                                type="checkbox"
                                name="competences[]"
                                value="<?= $idCompetence ?>"
                                id="competence_<?= $idCompetence ?>"
                                <?= in_array($idCompetence, $selectionnees, true) ? "checked" : "" ?>
                            >

                            <label
                                class="form-check-label"
                                for="competence_<?= $idCompetence ?>"
                            >
                                <?= htmlspecialchars($nomCompetence, ENT_QUOTES, "UTF-8") ?>
                            </label>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>
    </div>

    <button
        type="submit"
        class="btn w-100"
        style="background: var(--vert-feuille); color: var(--anthracite); border-radius: 14px; font-weight: 600; border: none; padding: 10px 20px;"
        <?= empty($competencesDisponibles) ? "disabled" : "" ?>
    >
        <?= t("volunteer_submit") ?>
    </button>

</form>

</div>
<?php endif; ?>

</div>

<script>
document.getElementById("formCandidature")?.addEventListener("submit", function (event) {
    const competences = document.querySelectorAll(".competence-checkbox:checked");

    if (competences.length === 0) {
        event.preventDefault();
        alert(
            <?= json_encode(
                $langue === "en"
                    ? "Please select at least one skill."
                    : "Veuillez sélectionner au moins une compétence.",
                JSON_UNESCAPED_UNICODE
            ) ?>
        );
    }
});
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>