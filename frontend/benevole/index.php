<?php
session_start();

<<<<<<< HEAD
require_once __DIR__ . "/../includes/auth.php";
exigerRole("BENEVOLE");

require_once __DIR__ . "/../includes/api.php";

$utilisateur = $_SESSION["utilisateur"];
$idUtilisateur = (int) ($utilisateur["id_utilisateur"] ?? 0);

$planning = API::get("/api/benevole/planning");
if (!is_array($planning)) $planning = [];

$collectes = $planning["collectes"] ?? [];
$services  = $planning["services"] ?? [];
if (!is_array($collectes)) $collectes = [];
if (!is_array($services))  $services = [];

$profil = API::get("/api/utilisateurs/get?id=" . $idUtilisateur);
if (!is_array($profil)) $profil = [];

function dateFrBenevole($date)
{
    if (!$date) return "Date à confirmer";
    $timestamp = strtotime($date);
    if (!$timestamp) return $date;
    $mois = [1 => "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"];
    return date("j", $timestamp) . " " . $mois[(int) date("n", $timestamp)] . " " . date("Y", $timestamp);
}

function badgeMission($statut)
{
    return match ($statut) {
        "VALIDEE", "PLANIFIEE", "OUVERT", "CONFIRME" => "nmw-badge nmw-badge-blue",
        "EN_COURS", "INSCRIT" => "nmw-badge nmw-badge-yellow",
        "TERMINEE" => "nmw-badge nmw-badge-green",
        "ANNULEE", "ANNULE" => "nmw-badge nmw-badge-gray",
        default => "nmw-badge nmw-badge-gray",
    };
}

$nbMissions = count($collectes);
$nbServices = count($services);

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navbar.php";
?>

<style>
:root {
    --nmw-bg: #F4F9F3;
    --nmw-green: #2E7D32;
    --nmw-blue: #2B7A9B;
    --nmw-leaf: #7BC96F;
    --nmw-sky: #B9E3F3;
    --nmw-yellow: #F2C94C;
    --nmw-text: #263238;
    --nmw-muted: #66767d;
    --nmw-white: #FFFFFF;
}

body { background: var(--nmw-bg); color: var(--nmw-text); }
.nmw-volunteer-shell { max-width: 1220px; margin: 0 auto; padding: 42px 24px 64px; }
.nmw-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #245f29 0%, #2E7D32 55%, #3187a8 130%);
    border-radius: 26px;
    color: #fff;
    padding: 38px;
    box-shadow: 0 18px 42px rgba(46, 125, 50, .18);
}
.nmw-hero::after {
    content: "";
    position: absolute;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: rgba(185, 227, 243, .16);
    right: -65px; top: -85px;
}
.nmw-kicker { font-size: .8rem; text-transform: uppercase; letter-spacing: .14em; font-weight: 800; opacity: .82; }
.nmw-hero h1 { color: white; font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 800; margin: 8px 0 10px; }
.nmw-hero p { max-width: 720px; margin: 0; opacity: .92; }
.nmw-summary { margin-top: -22px; position: relative; z-index: 2; }
.nmw-stat {
    background: #fff;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 10px 30px rgba(38, 50, 56, .09);
    height: 100%;
}
.nmw-stat-icon {
    width: 46px; height: 46px; border-radius: 14px;
    display: grid; place-items: center; margin-bottom: 14px;
    background: rgba(46, 125, 50, .1); color: var(--nmw-green); font-size: 1.2rem;
}
.nmw-stat.blue .nmw-stat-icon { background: rgba(43, 122, 155, .1); color: var(--nmw-blue); }
.nmw-stat.leaf .nmw-stat-icon { background: rgba(123, 201, 111, .18); color: #3e8a37; }
.nmw-stat strong { font-size: 1.75rem; display: block; line-height: 1; margin-bottom: 7px; }
.nmw-stat span { color: var(--nmw-muted); font-size: .92rem; }
.nmw-section-title { font-weight: 800; margin: 38px 0 18px; }
.nmw-panel {
    background: #fff;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 8px 28px rgba(38, 50, 56, .07);
    border: 1px solid rgba(46, 125, 50, .06);
}
.nmw-mission {
    display: flex;
    gap: 18px;
    padding: 18px 0;
    border-bottom: 1px solid #edf2ed;
}
.nmw-mission:last-child { border-bottom: 0; padding-bottom: 0; }
.nmw-date-box {
    width: 66px; min-width: 66px; height: 66px;
    border-radius: 16px;
    background: var(--nmw-bg);
    display: grid; place-items: center;
    color: var(--nmw-green);
    font-size: 1.25rem;
}
.nmw-mission h5 { font-weight: 750; margin-bottom: 6px; }
.nmw-meta { color: var(--nmw-muted); font-size: .92rem; display: flex; flex-wrap: wrap; gap: 10px 18px; }
.nmw-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 5px 10px; font-size: .76rem; font-weight: 800; }
.nmw-badge-blue { background: rgba(43,122,155,.12); color: #1f6682; }
.nmw-badge-yellow { background: rgba(242,201,76,.25); color: #765d00; }
.nmw-badge-green { background: rgba(123,201,111,.20); color: #2f7b2b; }
.nmw-badge-gray { background: #edf1ef; color: #68736d; }
.nmw-profile-line { display: flex; gap: 12px; margin-bottom: 14px; align-items: flex-start; }
.nmw-profile-line i { color: var(--nmw-blue); width: 18px; margin-top: 4px; }
.nmw-empty { text-align: center; padding: 30px 20px; color: var(--nmw-muted); }
.nmw-empty i { font-size: 2rem; color: var(--nmw-leaf); margin-bottom: 12px; }
.nmw-action {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--nmw-blue); color: #fff; text-decoration: none;
    padding: 10px 16px; border-radius: 12px; font-weight: 700;
}
.nmw-action:hover { background: #226985; color: #fff; }
@media (max-width: 767px) {
    .nmw-volunteer-shell { padding: 24px 14px 45px; }
    .nmw-hero { padding: 28px 22px; border-radius: 20px; }
    .nmw-summary { margin-top: 16px; }
    .nmw-mission { align-items: flex-start; }
}
</style>

<main class="nmw-volunteer-shell">
    <section class="nmw-hero">
        <div class="nmw-kicker">Espace bénévole</div>
        <h1>Bonjour <?= htmlspecialchars($utilisateur["prenom"] ?? "") ?> 👋</h1>
        <p>Retrouvez ici vos prochaines missions, vos inscriptions aux services et les informations utiles pour participer aux actions de No More Waste.</p>
    </section>

    <div class="row g-3 nmw-summary px-md-4">
        <div class="col-md-4">
            <div class="nmw-stat">
                <div class="nmw-stat-icon"><i class="fa-solid fa-truck"></i></div>
                <strong><?= $nbMissions ?></strong>
                <span>collecte<?= $nbMissions > 1 ? "s" : "" ?> dans mon planning</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="nmw-stat blue">
                <div class="nmw-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <strong><?= $nbServices ?></strong>
                <span>service<?= $nbServices > 1 ? "s" : "" ?> / atelier<?= $nbServices > 1 ? "s" : "" ?> inscrit<?= $nbServices > 1 ? "s" : "" ?></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="nmw-stat leaf">
                <div class="nmw-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                <strong>Actif</strong>
                <span>mon compte bénévole est validé</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <h2 class="nmw-section-title">Mon planning</h2>
            <div class="nmw-panel">
                <?php if (empty($collectes)): ?>
                    <div class="nmw-empty">
                        <i class="fa-solid fa-calendar-day"></i>
                        <div class="fw-bold text-dark mb-1">Aucune collecte affectée pour le moment</div>
                        <div>Lorsqu'un responsable vous affectera à une collecte, elle apparaîtra ici.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($collectes as $mission): ?>
                        <div class="nmw-mission">
                            <div class="nmw-date-box"><i class="fa-solid fa-truck"></i></div>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h5 class="mb-0">Collecte à <?= htmlspecialchars($mission["ville"] ?? "") ?></h5>
                                    <span class="<?= badgeMission($mission["statut"] ?? "") ?>"><?= htmlspecialchars($mission["statut"] ?? "") ?></span>
                                </div>
                                <div class="mb-2 fw-semibold"><?= htmlspecialchars(dateFrBenevole($mission["date_collecte"] ?? "")) ?></div>
                                <div class="nmw-meta">
                                    <span><i class="fa-regular fa-clock me-1"></i><?= htmlspecialchars($mission["heure_debut"] ?? "") ?> – <?= htmlspecialchars($mission["heure_fin"] ?? "") ?></span>
                                    <span><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars(($mission["adresse"] ?? "") . " " . ($mission["code_postal"] ?? "") . " " . ($mission["ville"] ?? "")) ?></span>
                                    <span><i class="fa-solid fa-user-tag me-1"></i>Rôle : <?= htmlspecialchars($mission["role_collecte"] ?? "") ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <h2 class="nmw-section-title">Mes services & ateliers</h2>
            <div class="nmw-panel">
                <?php if (empty($services)): ?>
                    <div class="nmw-empty">
                        <i class="fa-solid fa-seedling"></i>
                        <div class="fw-bold text-dark mb-1">Aucune inscription pour le moment</div>
                        <div class="mb-3">Vous pouvez consulter les services proposés par l'association.</div>
                        <a href="/public/services.php" class="nmw-action"><i class="fa-solid fa-arrow-right"></i> Découvrir les services</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <div class="nmw-mission">
                            <div class="nmw-date-box" style="color:var(--nmw-blue);"><i class="fa-solid fa-calendar-days"></i></div>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h5 class="mb-0"><?= htmlspecialchars($service["nom"] ?? "Service") ?></h5>
                                    <span class="<?= badgeMission($service["statut_inscription"] ?? "") ?>"><?= htmlspecialchars($service["statut_inscription"] ?? "") ?></span>
                                </div>
                                <div class="mb-2 fw-semibold"><?= htmlspecialchars(dateFrBenevole($service["date_service"] ?? "")) ?></div>
                                <div class="nmw-meta">
                                    <?php if (!empty($service["heure_debut"])): ?>
                                    <span><i class="fa-regular fa-clock me-1"></i><?= htmlspecialchars($service["heure_debut"]) ?> – <?= htmlspecialchars($service["heure_fin"] ?? "") ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($service["lieu"])): ?>
                                    <span><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($service["lieu"]) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <h2 class="nmw-section-title">Mon profil</h2>
            <aside class="nmw-panel">
                <div class="nmw-profile-line">
                    <i class="fa-solid fa-user"></i>
                    <div><small class="text-muted d-block">Identité</small><strong><?= htmlspecialchars(trim(($profil["prenom"] ?? "") . " " . ($profil["nom"] ?? ""))) ?></strong></div>
                </div>
                <div class="nmw-profile-line">
                    <i class="fa-solid fa-envelope"></i>
                    <div><small class="text-muted d-block">Email</small><?= htmlspecialchars($profil["email"] ?? "") ?></div>
                </div>
                <div class="nmw-profile-line">
                    <i class="fa-solid fa-phone"></i>
                    <div><small class="text-muted d-block">Téléphone</small><?= htmlspecialchars($profil["telephone"] ?? "Non renseigné") ?></div>
                </div>
                <div class="nmw-profile-line mb-0">
                    <i class="fa-solid fa-location-dot"></i>
                    <div><small class="text-muted d-block">Ville</small><?= htmlspecialchars($profil["ville"] ?? "Non renseignée") ?></div>
                </div>
            </aside>

            <h2 class="nmw-section-title">Besoin d'aide ?</h2>
            <aside class="nmw-panel">
                <p class="text-muted mb-3">Une mission n'apparaît pas ou vos horaires ne sont pas corrects ? Contactez un responsable de l'association.</p>
                <a href="/public/accueil.php" class="nmw-action"><i class="fa-solid fa-house"></i> Accueil</a>
            </aside>
        </div>
    </div>
</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>
=======
require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

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

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "delete"
) {
    $resultat = API::post("/api/benevoles/delete", [
        "id_utilisateur" => (int) ($_POST["id_utilisateur"] ?? 0)
    ]);

    if (is_array($resultat) && isset($resultat["message"])) {
        $message = $resultat["message"];
    } else {
        $error = "Impossible de supprimer le bénévole.";
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "update"
) {
    $idUtilisateur = (int) ($_POST["id_utilisateur"] ?? 0);
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

if (!is_array($benevoles)) {
    $benevoles = [];
    $error = "Impossible de récupérer les bénévoles.";
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

<h2>Gestion des bénévoles</h2>

<hr>

<p class="text-muted">
    Consultez et modifiez les informations, compétences et statuts des bénévoles.
</p>

<?php if ($message): ?>

<div class="alert alert-success">
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>

<?php if ($error): ?>

<div class="alert alert-danger">
    <?= htmlspecialchars($error) ?>
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

<td>
<?= htmlspecialchars($benevole["nom"] ?? "") ?>
</td>

<td>
<?= htmlspecialchars($benevole["prenom"] ?? "") ?>
</td>

<td>
<?= htmlspecialchars($benevole["email"] ?? "") ?>
</td>

<td>
<?= htmlspecialchars($benevole["telephone"] ?? "—") ?>
</td>

<td>
<?= htmlspecialchars($benevole["ville"] ?? "—") ?>
</td>

<td>

<?php if (!empty($benevole["actif"])): ?>

<span class="badge bg-success">
Actif
</span>

<?php else: ?>

<span class="badge bg-warning text-dark">
En attente
</span>

<?php endif; ?>

</td>

<td>

<?php if (!empty($benevole["competences"])): ?>

<?php foreach ($benevole["competences"] as $competence): ?>

<span class="badge bg-info text-dark me-1 mb-1">
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

<div class="d-flex gap-2 flex-wrap">

<button
    type="button"
    class="btn btn-warning btn-sm"
    onclick='ouvrirModification(
        <?= json_encode(
            $benevole,
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_AMP |
            JSON_HEX_TAG
        ) ?>
    )'
>
Modifier
</button>

<form
    method="post"
    onsubmit="return confirm(
        'Voulez-vous vraiment supprimer ce bénévole ? Cette action est définitive.'
    );"
>

<input
    type="hidden"
    name="action"
    value="delete"
>

<input
    type="hidden"
    name="id_utilisateur"
    value="<?= (int) ($benevole["id_utilisateur"] ?? 0) ?>"
>

<button
    type="submit"
    class="btn btn-danger btn-sm"
>
Supprimer
</button>

</form>

</div>

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

<div
    class="modal fade"
    id="editModal"
    tabindex="-1"
    aria-hidden="true"
>

<div class="modal-dialog">

<div class="modal-content">

<form method="POST">

<input
    type="hidden"
    name="action"
    value="update"
>

<div class="modal-header">

<h5 class="modal-title">
Modifier le bénévole
</h5>

<button
    type="button"
    class="btn-close"
    data-bs-dismiss="modal"
></button>

</div>

<div class="modal-body">

<input
    type="hidden"
    name="id_utilisateur"
    id="id_utilisateur"
>

<div class="mb-3">

<label class="form-label">
Nom
</label>

<input
    type="text"
    class="form-control"
    name="nom"
    id="nom"
    required
>

</div>

<div class="mb-3">

<label class="form-label">
Prénom
</label>

<input
    type="text"
    class="form-control"
    name="prenom"
    id="prenom"
    required
>

</div>

<div class="mb-3">

<label class="form-label">
Email
</label>

<input
    type="email"
    class="form-control"
    name="email"
    id="email"
    required
>

</div>

<div class="mb-3">

<label class="form-label">
Téléphone
</label>

<input
    type="text"
    class="form-control"
    name="telephone"
    id="telephone"
>

</div>

<div class="mb-3">

<label class="form-label">
Ville
</label>

<input
    type="text"
    class="form-control"
    name="ville"
    id="ville"
>

</div>

<div class="form-check mb-3">

<input
    type="checkbox"
    class="form-check-input"
    name="actif"
    id="actif"
>

<label
    class="form-check-label"
    for="actif"
>
Bénévole actif
</label>

</div>

<div class="mb-3">

<label class="form-label">
Compétences
</label>

<?php foreach ($competencesDisponibles as $id => $nom): ?>

<div class="form-check">

<input
    type="checkbox"
    class="form-check-input competence-checkbox"
    name="competences[]"
    value="<?= $id ?>"
    id="competence_<?= $id ?>"
    data-nom="<?= htmlspecialchars($nom) ?>"
>

<label
    class="form-check-label"
    for="competence_<?= $id ?>"
>
<?= htmlspecialchars($nom) ?>
</label>

</div>

<?php endforeach; ?>

</div>

</div>

<div class="modal-footer">

<button
    type="button"
    class="btn btn-secondary"
    data-bs-dismiss="modal"
>
Annuler
</button>

<button
    type="submit"
    class="btn btn-success"
>
Enregistrer
</button>

</div>

</form>

</div>

</div>

</div>

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

    const checkboxes =
        document.querySelectorAll(
            ".competence-checkbox"
        );

    checkboxes.forEach(function(checkbox) {

        checkbox.checked = false;

        if (
            Array.isArray(benevole.competences) &&
            benevole.competences.includes(
                checkbox.dataset.nom
            )
        ) {
            checkbox.checked = true;
        }

    });

    const modal = new bootstrap.Modal(
        document.getElementById("editModal")
    );

    modal.show();
}

</script>

<?php include "../includes/footer.php"; ?>
>>>>>>> 897b1b168323516e3dec6db4b199617fe46980ac
