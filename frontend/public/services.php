<?php
session_start();

require_once __DIR__ . "/../includes/api.php";
require_once __DIR__ . "/../includes/lang.php";

$messageSucces = null;
$messageErreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reponse = API::post("/api/public/inscription", [
        "nom"        => trim($_POST["nom"] ?? ""),
        "prenom"     => trim($_POST["prenom"] ?? ""),
        "email"      => trim($_POST["email"] ?? ""),
        "id_service" => (int) ($_POST["id_service"] ?? 0),
    ]);

    if (isset($reponse["message"])) {
        $messageSucces = t("services_success");
    } else {
        $messageErreur = t("services_error");
    }
}

$services = API::get("/api/public/services");
if (!is_array($services)) {
    $services = [];
}

function nmwTexteAffichage($valeur)
{
    $texte = (string) ($valeur ?? "");

    if ($texte !== "" && (str_contains($texte, "Ã") || str_contains($texte, "Â"))) {
        $corrige = @iconv("UTF-8", "Windows-1252//IGNORE", $texte);

        if ($corrige !== false && $corrige !== "") {
            return $corrige;
        }
    }

    return $texte;
}

function nmwDateAffichage($valeur)
{
    if (!$valeur) {
        return t("date_to_confirm");
    }

    $date = substr((string) $valeur, 0, 10);
    $morceaux = explode("-", $date);

    if (count($morceaux) !== 3) {
        return $date;
    }

    $mois = [
        "01" => t("month_january"),
        "02" => t("month_february"),
        "03" => t("month_march"),
        "04" => t("month_april"),
        "05" => t("month_may"),
        "06" => t("month_june"),
        "07" => t("month_july"),
        "08" => t("month_august"),
        "09" => t("month_september"),
        "10" => t("month_october"),
        "11" => t("month_november"),
        "12" => t("month_december"),
    ];

    [$annee, $numeroMois, $jour] = $morceaux;

    return (int) $jour . " " . ($mois[$numeroMois] ?? $numeroMois) . " " . $annee;
}

include __DIR__ . "/../includes/header.php";
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto+Condensed:wght@600;700&display=swap" rel="stylesheet">

<style>
:root {
    --nmw-brume: #F4F9F3;
    --nmw-vert-foret: #2E7D32;
    --nmw-vert-foret-fonce: #245E28;
    --nmw-bleu-planete: #2B7A9B;
    --nmw-vert-feuille: #7BC96F;
    --nmw-bleu-ciel: #B9E3F3;
    --nmw-jaune: #F2C94C;
    --nmw-blanc: #FFFFFF;
    --nmw-anthracite: #263238;
    --nmw-muted: #68766E;
    --nmw-border: rgba(38, 50, 56, .10);
    --nmw-shadow: 0 18px 45px rgba(35, 83, 51, .10);
    --nmw-shadow-hover: 0 24px 64px rgba(35, 83, 51, .16);
}

body {
    min-height: 100vh;
    background:
        radial-gradient(circle at 8% 15%, rgba(123, 201, 111, .17), transparent 26%),
        radial-gradient(circle at 92% 20%, rgba(185, 227, 243, .34), transparent 29%),
        linear-gradient(180deg, #F8FCF7 0%, var(--nmw-brume) 100%);
    color: var(--nmw-anthracite);
    font-family: "Inter", sans-serif;
}

.nmw-public-nav {
    position: sticky;
    top: 0;
    z-index: 1030;
    padding: 13px 0;
    background: rgba(46, 125, 50, .96);
    border-bottom: 1px solid rgba(255,255,255,.14);
    box-shadow: 0 8px 30px rgba(28, 70, 37, .14);
    backdrop-filter: blur(14px);
}

.nmw-public-nav .navbar-brand {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #fff;
    font-family: "Roboto Condensed", sans-serif;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: -.01em;
}

.nmw-public-nav .navbar-brand:hover { color: #fff; }

.nmw-brand-mark {
    display: inline-flex;
    width: 36px;
    height: 36px;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.20);
}

.nmw-nav-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.nmw-member-btn,
.nmw-lang-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    border: 1px solid rgba(255,255,255,.72);
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    transition: .2s ease;
}

.nmw-lang-btn {
    min-width: 44px;
    justify-content: center;
    padding-left: 10px;
    padding-right: 10px;
}

.nmw-member-btn:hover,
.nmw-lang-btn:hover,
.nmw-lang-btn.active {
    color: var(--nmw-vert-foret);
    background: #fff;
    border-color: #fff;
    transform: translateY(-1px);
}

.nmw-services-page {
    padding: 70px 0 90px;
}

.nmw-services-hero {
    position: relative;
    margin-bottom: 42px;
}

.nmw-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
    padding: 8px 12px;
    border: 1px solid rgba(43, 122, 155, .16);
    border-radius: 999px;
    background: rgba(255,255,255,.75);
    color: var(--nmw-bleu-planete);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    box-shadow: 0 8px 24px rgba(43,122,155,.06);
}

.nmw-services-title {
    max-width: 760px;
    margin: 0 0 14px;
    font-family: "Roboto Condensed", sans-serif;
    font-size: clamp(42px, 6vw, 68px);
    line-height: .98;
    font-weight: 700;
    letter-spacing: -.035em;
    color: var(--nmw-anthracite);
}

.nmw-services-title span { color: var(--nmw-vert-foret); }

.nmw-services-intro {
    max-width: 700px;
    margin: 0;
    color: var(--nmw-muted);
    font-size: 17px;
    line-height: 1.7;
}

.nmw-hero-note {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    margin-top: 18px;
    color: var(--nmw-anthracite);
    font-size: 14px;
    font-weight: 600;
}

.nmw-hero-note i { color: var(--nmw-vert-foret); }

.nmw-alert {
    border: 0;
    border-radius: 16px;
    padding: 15px 18px;
    box-shadow: 0 12px 32px rgba(35,83,51,.08);
}

.nmw-empty {
    padding: 36px;
    text-align: center;
    border: 1px dashed rgba(46,125,50,.26);
    border-radius: 18px;
    background: rgba(255,255,255,.78);
    color: var(--nmw-muted);
}

.nmw-empty i {
    display: block;
    margin-bottom: 13px;
    color: var(--nmw-vert-feuille);
    font-size: 34px;
}

.nmw-service-card {
    position: relative;
    height: 100%;
    overflow: hidden;
    border: 1px solid var(--nmw-border);
    border-radius: 20px;
    background: rgba(255,255,255,.94);
    box-shadow: var(--nmw-shadow);
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}

.nmw-service-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 5px;
    background: linear-gradient(180deg, var(--nmw-vert-foret), var(--nmw-vert-feuille));
}

.nmw-service-card::after {
    content: "";
    position: absolute;
    width: 150px;
    height: 150px;
    top: -90px;
    right: -70px;
    border-radius: 50%;
    background: rgba(185, 227, 243, .36);
    pointer-events: none;
}

.nmw-service-card:hover {
    transform: translateY(-7px);
    border-color: rgba(46,125,50,.18);
    box-shadow: var(--nmw-shadow-hover);
}

.nmw-service-card-body {
    position: relative;
    z-index: 1;
    display: flex;
    height: 100%;
    flex-direction: column;
    padding: 26px;
}

.nmw-service-topline {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.nmw-service-icon {
    display: inline-flex;
    width: 48px;
    height: 48px;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    background: rgba(123,201,111,.18);
    color: var(--nmw-vert-foret);
    font-size: 20px;
}

.nmw-service-badge {
    padding: 7px 10px;
    border-radius: 999px;
    background: rgba(185,227,243,.40);
    color: #23617C;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
}

.nmw-service-name {
    margin: 0 0 9px;
    font-family: "Roboto Condensed", sans-serif;
    font-size: 27px;
    line-height: 1.12;
    font-weight: 700;
    color: var(--nmw-anthracite);
}

.nmw-service-description {
    min-height: 72px;
    margin: 0 0 20px;
    color: var(--nmw-muted);
    line-height: 1.6;
}

.nmw-service-meta {
    display: grid;
    gap: 10px;
    margin-bottom: 22px;
}

.nmw-meta-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    color: #405047;
    font-size: 14px;
}

.nmw-meta-row i {
    width: 18px;
    margin-top: 3px;
    color: var(--nmw-bleu-planete);
    text-align: center;
}

.nmw-capacity {
    display: inline-flex;
    width: fit-content;
    align-items: center;
    gap: 7px;
    margin-bottom: 22px;
    padding: 8px 10px;
    border-radius: 10px;
    background: #F7FAF7;
    border: 1px solid var(--nmw-border);
    color: var(--nmw-muted);
    font-size: 12px;
    font-weight: 600;
}

.nmw-service-action {
    display: inline-flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 9px;
    margin-top: auto;
    padding: 12px 16px;
    border: 0;
    border-radius: 14px;
    background: var(--nmw-vert-foret);
    color: #fff;
    font-weight: 700;
    box-shadow: 0 10px 24px rgba(46,125,50,.20);
    transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
}

.nmw-service-action:hover {
    color: #fff;
    background: var(--nmw-vert-foret-fonce);
    transform: translateY(-1px);
    box-shadow: 0 13px 28px rgba(46,125,50,.24);
}

.nmw-modal .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 20px;
    box-shadow: 0 28px 80px rgba(38,50,56,.20);
}

.nmw-modal .modal-header {
    padding: 22px 24px;
    border-bottom: 1px solid var(--nmw-border);
    background: linear-gradient(135deg, rgba(123,201,111,.15), rgba(185,227,243,.28));
}

.nmw-modal .modal-title {
    font-family: "Roboto Condensed", sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--nmw-anthracite);
}

.nmw-modal .modal-body { padding: 24px; }
.nmw-modal .modal-footer { padding: 0 24px 24px; border-top: 0; }

.nmw-modal .form-label {
    color: #405047;
    font-size: 13px;
    font-weight: 700;
}

.nmw-modal .form-control {
    min-height: 46px;
    border: 1px solid rgba(38,50,56,.14);
    border-radius: 12px;
    background: #FCFEFC;
    box-shadow: none;
}

.nmw-modal .form-control:focus {
    border-color: rgba(46,125,50,.52);
    box-shadow: 0 0 0 .22rem rgba(46,125,50,.10);
}

.nmw-modal-submit {
    width: 100%;
    padding: 12px 16px;
    border: 0;
    border-radius: 14px;
    background: var(--nmw-vert-foret);
    color: #fff;
    font-weight: 700;
}

@media (max-width: 767.98px) {
    .nmw-services-page { padding: 48px 0 64px; }
    .nmw-public-nav { padding: 10px 0; }
    .nmw-public-nav .navbar-brand { font-size: 21px; }
    .nmw-services-title { font-size: 44px; }
    .nmw-service-description { min-height: 0; }
}
</style>

<nav class="navbar nmw-public-nav">
    <div class="container-fluid px-3 px-md-4">
        <a class="navbar-brand fw-bold" href="/public/accueil.php">
            <span class="nmw-brand-mark"><i class="fa-solid fa-leaf"></i></span>
            <span>No More Waste</span>
        </a>

        <div class="nmw-nav-actions">
            <a href="/login.php" class="nmw-member-btn">
                <i class="fa-regular fa-user"></i>
                <span><?= t("member_area") ?></span>
            </a>

            <a href="?lang=fr" class="nmw-lang-btn <?= ($_SESSION["lang"] ?? "fr") === "fr" ? "active" : "" ?>">
                FR
            </a>

            <a href="?lang=en" class="nmw-lang-btn <?= ($_SESSION["lang"] ?? "fr") === "en" ? "active" : "" ?>">
                EN
            </a>
        </div>
    </div>
</nav>

<main class="nmw-services-page">
<div class="container">

<section class="nmw-services-hero">
    <div class="nmw-eyebrow">
        <i class="fa-solid fa-seedling"></i>
        <?= t("services_eyebrow") ?>
    </div>

    <h1 class="nmw-services-title">
        <?= t("services_title_1") ?> <span><?= t("services_title_2") ?></span>
    </h1>

    <p class="nmw-services-intro">
        <?= t("services_intro") ?>
    </p>

    <div class="nmw-hero-note">
        <i class="fa-solid fa-circle-check"></i>
        <?= t("services_free_registration") ?>
    </div>
</section>

<?php if ($messageSucces): ?>
    <div class="alert alert-success nmw-alert">
        <i class="fa-solid fa-circle-check me-2"></i>
        <?= htmlspecialchars($messageSucces, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if ($messageErreur): ?>
    <div class="alert alert-danger nmw-alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        <?= htmlspecialchars($messageErreur, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (empty($services)): ?>

    <div class="nmw-empty">
        <i class="fa-regular fa-calendar"></i>
        <strong class="d-block mb-1"><?= t("services_empty_title") ?></strong>
        <span><?= t("services_empty_text") ?></span>
    </div>

<?php else: ?>

<div class="row g-4">

<?php foreach ($services as $s):
    $nomService = nmwTexteAffichage($s["nom"] ?? "");
    $descriptionService = nmwTexteAffichage($s["description"] ?? "");
    $lieuService = nmwTexteAffichage($s["lieu"] ?? t("place_to_confirm"));
    $dateService = nmwDateAffichage($s["date_service"] ?? null);
    $heureDebut = substr((string) ($s["heure_debut"] ?? ""), 0, 5);
    $heureFin = substr((string) ($s["heure_fin"] ?? ""), 0, 5);
    $capaciteMax = isset($s["capacite_max"]) && $s["capacite_max"] !== null ? (int) $s["capacite_max"] : null;
    $nbInscrits = (int) ($s["nb_inscrits"] ?? 0);
?>

<div class="col-12 col-md-6 col-xl-4">
    <article class="nmw-service-card">
        <div class="nmw-service-card-body">

            <div class="nmw-service-topline">
                <span class="nmw-service-icon">
                    <i class="fa-solid fa-hands-helping"></i>
                </span>

                <span class="nmw-service-badge">
                    <?= t("services_solidarity_badge") ?>
                </span>
            </div>

            <h2 class="nmw-service-name">
                <?= htmlspecialchars($nomService, ENT_QUOTES, 'UTF-8') ?>
            </h2>

            <p class="nmw-service-description">
                <?= htmlspecialchars($descriptionService, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <div class="nmw-service-meta">
                <div class="nmw-meta-row">
                    <i class="fa-solid fa-location-dot"></i>
                    <span><?= htmlspecialchars($lieuService, ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="nmw-meta-row">
                    <i class="fa-regular fa-calendar"></i>
                    <span><?= htmlspecialchars($dateService, ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="nmw-meta-row">
                    <i class="fa-regular fa-clock"></i>
                    <span>
                        <?= htmlspecialchars($heureDebut, ENT_QUOTES, 'UTF-8') ?>
                        <?= $heureFin !== "" ? " – " . htmlspecialchars($heureFin, ENT_QUOTES, 'UTF-8') : "" ?>
                    </span>
                </div>
            </div>

            <?php if ($capaciteMax !== null): ?>
                <div class="nmw-capacity">
                    <i class="fa-solid fa-users"></i>
                    <?= $nbInscrits ?> / <?= $capaciteMax ?>
                    <?= $capaciteMax > 1 ? t("participants_plural") : t("participant_singular") ?>
                </div>
            <?php endif; ?>

            <button
                type="button"
                class="nmw-service-action"
                data-bs-toggle="modal"
                data-bs-target="#modalInscription"
                onclick="choisirService(<?= (int) $s["id_service"] ?>, <?= htmlspecialchars(json_encode($nomService, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>)"
            >
                <span><?= t("services_register") ?></span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>

        </div>
    </article>
</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
</main>

<div class="modal fade nmw-modal" id="modalInscription" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<form method="post">

<div class="modal-header">
    <h5 class="modal-title">
        <?= t("services_modal_title") ?>
        <span id="nomServiceChoisi"></span>
    </h5>

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="modal"
        aria-label="<?= htmlspecialchars(t("close"), ENT_QUOTES, 'UTF-8') ?>"
    ></button>
</div>

<div class="modal-body">

<input type="hidden" name="id_service" id="idServiceChoisi">

<div class="mb-3">
    <label class="form-label"><?= t("first_name") ?></label>
    <input type="text" name="prenom" class="form-control" required>
</div>

<div class="mb-3">
    <label class="form-label"><?= t("last_name") ?></label>
    <input type="text" name="nom" class="form-control" required>
</div>

<div class="mb-0">
    <label class="form-label"><?= t("email") ?></label>
    <input type="email" name="email" class="form-control" required>
</div>

</div>

<div class="modal-footer">
    <button type="submit" class="nmw-modal-submit">
        <?= t("services_confirm_registration") ?>
    </button>
</div>

</form>

</div>
</div>
</div>

<script>
function choisirService(id, nom) {
    document.getElementById("idServiceChoisi").value = id;
    document.getElementById("nomServiceChoisi").textContent = nom;
}
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>