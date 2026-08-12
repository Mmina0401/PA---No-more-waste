<?php
session_start();

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/api.php";

exigerRole("ADMIN", "RESPONSABLE");

$message = "";
$error = "";

$competencesDisponibles = [
    1 => "Chauffeur",
    2 => "Cuisine",
    3 => "Plomberie",
    4 => "Électricité",
    5 => "Bricolage",
    6 => "Logistique",
    7 => "Manutention"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idUtilisateur = intval($_POST["id_utilisateur"] ?? 0);
    $nom = trim($_POST["nom"] ?? "");
    $prenom = trim($_POST["prenom"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $ville = trim($_POST["ville"] ?? "");
    $actif = isset($_POST["actif"]);
    $competences = $_POST["competences"] ?? [];
    $competences = array_map("intval", $competences);

    $resultat = API::post("/api/benevoles/update", [
        "id_utilisateur" => $idUtilisateur,
        "nom" => $nom,
        "prenom" => $prenom,
        "email" => $email,
        "telephone" => $telephone,
        "ville" => $ville,
        "actif" => $actif,
        "competences" => $competences
    ]);

    if (is_array($resultat) && isset($resultat["message"])) {
        $message = $resultat["message"];
    } else {
        $error = "Impossible de modifier le bénévole.";
    }
}

$benevoles = API::get("/api/benevoles");

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navbar.php";
?>

<style>
:root {
    --nmw-bg: #F4F9F3;
    --nmw-green: #2E7D32;
    --nmw-blue: #2B7A9B;
    --nmw-leaf: #7BC96F;
    --nmw-yellow: #F2C94C;
    --nmw-text: #263238;
    --nmw-muted: #66767d;
    --nmw-white: #FFFFFF;
}

body {
    background: var(--nmw-bg);
    color: var(--nmw-text);
}

.nmw-shell {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 24px 60px;
}

.nmw-title {
    color: var(--nmw-green);
    font-weight: 800;
    margin-bottom: 8px;
}

.nmw-subtitle {
    color: var(--nmw-muted);
    margin-bottom: 24px;
}

.nmw-alert-success {
    background: rgba(123, 201, 111, .25);
    color: #2f6f2d;
    padding: 14px 18px;
    border-radius: 14px;
    margin-bottom: 20px;
}

.nmw-alert-error {
    background: rgba(242, 201, 76, .28);
    color: #765d00;
    padding: 14px 18px;
    border-radius: 14px;
    margin-bottom: 20px;
}

.nmw-table-wrap {
    background: white;
    border-radius: 18px;
    overflow-x: auto;
    box-shadow: 0 8px 28px rgba(38, 50, 56, .07);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: var(--nmw-green);
    color: white;
    padding: 14px;
    text-align: left;
    white-space: nowrap;
}

td {
    padding: 14px;
    border-bottom: 1px solid #edf2ed;
    vertical-align: middle;
}

tr:hover {
    background: #f8fbf7;
}

.nmw-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 5px 10px;
    font-size: .78rem;
    font-weight: 800;
}

.nmw-badge-active {
    background: rgba(123, 201, 111, .22);
    color: #2f7b2b;
}

.nmw-badge-waiting {
    background: rgba(242, 201, 76, .28);
    color: #765d00;
}

.nmw-skill {
    display: inline-block;
    background: rgba(43, 122, 155, .12);
    color: #1f6682;
    padding: 4px 8px;
    border-radius: 999px;
    margin: 2px;
    font-size: .78rem;
    font-weight: 700;
}

.nmw-btn {
    background: var(--nmw-blue);
    color: white;
    border: none;
    padding: 8px 13px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
}

.nmw-btn:hover {
    background: #226985;
}

dialog {
    border: none;
    border-radius: 18px;
    width: min(520px, 92vw);
    padding: 26px;
}

dialog::backdrop {
    background: rgba(0, 0, 0, .45);
}

.nmw-field {
    margin-bottom: 15px;
}

.nmw-field label {
    display: block;
    font-weight: 700;
    margin-bottom: 6px;
}

.nmw-field input[type="text"],
.nmw-field input[type="email"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccd6ce;
    border-radius: 10px;
    box-sizing: border-box;
}

.nmw-skill-line {
    margin: 7px 0;
}

.nmw-dialog-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
}

.nmw-btn-save {
    background: var(--nmw-green);
    color: white;
    border: none;
    padding: 9px 15px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
}

.nmw-btn-cancel {
    background: #e7ece8;
    color: var(--nmw-text);
    border: none;
    padding: 9px 15px;
    border-radius: 10px;
    cursor: pointer;
}
</style>

<main class="nmw-shell">

    <h1 class="nmw-title">Gestion des bénévoles</h1>
    <p class="nmw-subtitle">
        Consultez et modifiez les informations, compétences et statuts des bénévoles.
    </p>

    <?php if ($message): ?>
        <div class="nmw-alert-success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="nmw-alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!is_array($benevoles)): ?>

        <div class="nmw-alert-error">
            Impossible de récupérer les bénévoles.
        </div>

    <?php else: ?>

        <div class="nmw-table-wrap">

            <table>
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Ville</th>
                    <th>Statut</th>
                    <th>Compétences</th>
                    <th>Actions</th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($benevoles as $benevole): ?>

                    <tr>

                        <td><?= htmlspecialchars($benevole["nom"] ?? "") ?></td>

                        <td><?= htmlspecialchars($benevole["prenom"] ?? "") ?></td>

                        <td><?= htmlspecialchars($benevole["email"] ?? "") ?></td>

                        <td>
                            <?= htmlspecialchars($benevole["telephone"] ?? "Non renseigné") ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($benevole["ville"] ?? "Non renseignée") ?>
                        </td>

                        <td>
                            <?php if (!empty($benevole["actif"])): ?>
                                <span class="nmw-badge nmw-badge-active">
                                    Actif
                                </span>
                            <?php else: ?>
                                <span class="nmw-badge nmw-badge-waiting">
                                    En attente
                                </span>
                            <?php endif; ?>
                        </td>

                        <td>

                            <?php if (!empty($benevole["competences"])): ?>

                                <?php foreach ($benevole["competences"] as $competence): ?>

                                    <span class="nmw-skill">
                                        <?= htmlspecialchars($competence) ?>
                                    </span>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <span class="text-muted">
                                    Aucune
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <button
                                class="nmw-btn"
                                onclick='ouvrirModification(
                                    <?= json_encode(
                                        $benevole,
                                        JSON_HEX_APOS | JSON_HEX_QUOT
                                    ) ?>
                                )'
                            >
                                Modifier
                            </button>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

        </div>

    <?php endif; ?>

</main>

<dialog id="editDialog">

    <h2 class="mb-4">
        Modifier le bénévole
    </h2>

    <form method="POST">

        <input
            type="hidden"
            name="id_utilisateur"
            id="id_utilisateur"
        >

        <div class="nmw-field">
            <label>Nom</label>
            <input
                type="text"
                name="nom"
                id="nom"
                required
            >
        </div>

        <div class="nmw-field">
            <label>Prénom</label>
            <input
                type="text"
                name="prenom"
                id="prenom"
                required
            >
        </div>

        <div class="nmw-field">
            <label>Email</label>
            <input
                type="email"
                name="email"
                id="email"
                required
            >
        </div>

        <div class="nmw-field">
            <label>Téléphone</label>
            <input
                type="text"
                name="telephone"
                id="telephone"
            >
        </div>

        <div class="nmw-field">
            <label>Ville</label>
            <input
                type="text"
                name="ville"
                id="ville"
            >
        </div>

        <div class="nmw-field">

            <label>
                <input
                    type="checkbox"
                    name="actif"
                    id="actif"
                >
                Bénévole actif
            </label>

        </div>

        <div class="nmw-field">

            <label>
                Compétences
            </label>

            <?php foreach ($competencesDisponibles as $id => $nom): ?>

                <div class="nmw-skill-line">

                    <label>

                        <input
                            type="checkbox"
                            name="competences[]"
                            value="<?= $id ?>"
                            data-nom="<?= htmlspecialchars($nom) ?>"
                        >

                        <?= htmlspecialchars($nom) ?>

                    </label>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="nmw-dialog-actions">

            <button
                type="button"
                class="nmw-btn-cancel"
                onclick="fermerModification()"
            >
                Annuler
            </button>

            <button
                type="submit"
                class="nmw-btn-save"
            >
                Enregistrer
            </button>

        </div>

    </form>

</dialog>

<script>
function ouvrirModification(benevole) {
    document.getElementById("id_utilisateur").value =
        benevole.id_utilisateur ?? "";

    document.getElementById("nom").value =
        benevole.nom ?? "";

    document.getElementById("prenom").value =
        benevole.prenom ?? "";

    document.getElementById("email").value =
        benevole.email ?? "";

    document.getElementById("telephone").value =
        benevole.telephone ?? "";

    document.getElementById("ville").value =
        benevole.ville ?? "";

    document.getElementById("actif").checked =
        benevole.actif === true;

    const checkboxes = document.querySelectorAll(
        '#editDialog input[name="competences[]"]'
    );

    checkboxes.forEach(function(checkbox) {
        checkbox.checked = false;

        if (
            Array.isArray(benevole.competences) &&
            benevole.competences.includes(checkbox.dataset.nom)
        ) {
            checkbox.checked = true;
        }
    });

    document
        .getElementById("editDialog")
        .showModal();
}

function fermerModification() {
    document
        .getElementById("editDialog")
        .close();
}
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>