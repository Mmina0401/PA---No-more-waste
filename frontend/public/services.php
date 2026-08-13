<?php
session_start();

require_once __DIR__ . "/../includes/api.php";
require_once __DIR__ . "/../includes/lang.php";

$messageSucces = null;
$messageErreur = null;

$utilisateur = $_SESSION["utilisateur"] ?? null;
$estConnecte = isset($_SESSION["jeton"]) && is_array($utilisateur);
$role = strtoupper($utilisateur["role"] ?? "");
$estBenevole = $estConnecte && $role === "BENEVOLE";
$estAdmin = $estConnecte && in_array($role, ["ADMIN", "RESPONSABLE"], true);

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "inscription_service"
) {
    if (!$estBenevole) {
        $messageErreur = "Vous devez être connecté avec un compte bénévole pour participer.";
    } else {
        $idService = (int) ($_POST["id_service"] ?? 0);

        if ($idService <= 0) {
            $messageErreur = "Service invalide.";
        } else {
            $reponse = API::post("/api/public/inscription", [
                "nom" => $utilisateur["nom"] ?? "",
                "prenom" => $utilisateur["prenom"] ?? "",
                "email" => $utilisateur["email"] ?? "",
                "id_service" => $idService
            ]);

            if (is_array($reponse) && isset($reponse["message"])) {
                $messageSucces = "Votre inscription au service a bien été enregistrée.";
            } else {
                $messageErreur = "Impossible de vous inscrire à ce service. Vous êtes peut-être déjà inscrit ou le service est complet.";
            }
        }
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
    box-shadow: 0 8px 30px rgba(28,70,37,.14);
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
    text-decoration: none;
}

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

.nmw-member-btn:hover,
.nmw-lang-btn:hover,
.nmw-lang-btn.active {
    color: var(--nmw-vert-foret);
    background: #fff;
    border-color: #fff;
}

.nmw-services-page {
    padding: 70px 0 90px;
}

.nmw-services-hero {
    margin-bottom: 42px;
}

.nmw-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
    padding: 8px 12px;
    border: 1px solid rgba(43,122,155,.16);
    border-radius: 999px;
    background: rgba(255,255,255,.75);
    color: var(--nmw-bleu-planete);
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.nmw-services-title {
    max-width: 760px;
    margin: 0 0 14px;
    font-family: "Roboto Condensed", sans-serif;
    font-size: clamp(42px, 6vw, 68px);
    line-height: .98;
    font-weight: 700;
    color: var(--nmw-anthracite);
}

.nmw-services-title span {
    color: var(--nmw-vert-foret);
}

.nmw-services-intro {
    max-width: 700px;
    color: var(--nmw-muted);
    font-size: 17px;
    line-height: 1.7;
}

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

.nmw-service-card {
    position: relative;
    height: 100%;
    overflow: hidden;
    border: 1px solid var(--nmw-border);
    border-radius: 20px;
    background: rgba(255,255,255,.94);
    box-shadow: var(--nmw-shadow);
    transition: .25s ease;
}

.nmw-service-card:hover {
    transform: translateY(-7px);
    box-shadow: var(--nmw-shadow-hover);
}

.nmw-service-card-body {
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
    text-transform: uppercase;
}

.nmw-service-name {
    margin: 0 0 9px;
    font-family: "Roboto Condensed", sans-serif;
    font-size: 27px;
    font-weight: 700;
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
    gap: 10px;
    color: #405047;
    font-size: 14px;
}

.nmw-meta-row i {
    width: 18px;
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
    text-decoration: none;
    box-shadow: 0 10px 24px rgba(46,125,50,.20);
}

.nmw-service-action:hover {
    color: #fff;
    background: var(--nmw-vert-foret-fonce);
}

.nmw-service-action.blue {
    background: var(--nmw-bleu-planete);
}

.nmw-service-action.gray {
    background: #607D8B;
}

@media (max-width: 767.98px) {
    .nmw-services-page {
        padding: 48px 0 64px;
    }

    .nmw-service-description {
        min-height: 0;
    }
}
</style>

<nav class="navbar nmw-public-nav">
<div class="container-fluid px-3 px-md-4">

<a class="navbar-brand" href="/public/accueil.php">
    <span class="nmw-brand-mark">
        <i class="fa-solid fa-leaf"></i>
    </span>
    <span>No More Waste</span>
</a>

<div class="nmw-nav-actions">

<?php if (!$estConnecte): ?>

<a href="/login.php" class="nmw-member-btn">
    <i class="fa-regular fa-user"></i>
    <span>Se connecter</span>
</a>

<?php elseif ($estBenevole): ?>

<a href="/benevole/dashboard.php" class="nmw-member-btn">
    <i class="fa-solid fa-user"></i>
    <span>Mon espace bénévole</span>
</a>

<?php else: ?>

<a href="/dashboard.php" class="nmw-member-btn">
    <i class="fa-solid fa-gauge"></i>
    <span>Mon espace</span>
</a>

<?php endif; ?>

<a
    href="?lang=fr"
    class="nmw-lang-btn <?= ($_SESSION["lang"] ?? "fr") === "fr" ? "active" : "" ?>"
>
    FR
</a>

<a
    href="?lang=en"
    class="nmw-lang-btn <?= ($_SESSION["lang"] ?? "fr") === "en" ? "active" : "" ?>"
>
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
    <?= t("services_title_1") ?>
    <span><?= t("services_title_2") ?></span>
</h1>

<p class="nmw-services-intro">
    <?= t("services_intro") ?>
</p>

</section>

<?php if ($messageSucces): ?>
<div class="alert alert-success nmw-alert">
    <i class="fa-solid fa-circle-check me-2"></i>
    <?= htmlspecialchars($messageSucces) ?>
</div>
<?php endif; ?>

<?php if ($messageErreur): ?>
<div class="alert alert-danger nmw-alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i>
    <?= htmlspecialchars($messageErreur) ?>
</div>
<?php endif; ?>

<?php if (empty($services)): ?>

<div class="nmw-empty">
    <i class="fa-regular fa-calendar"></i>
    <strong class="d-block mb-1">
        <?= t("services_empty_title") ?>
    </strong>
    <span><?= t("services_empty_text") ?></span>
</div>

<?php else: ?>

<div class="row g-4">

<?php foreach ($services as $s): ?>

<?php
$idService = (int) ($s["id_service"] ?? 0);
$nomService = nmwTexteAffichage($s["nom"] ?? "");
$descriptionService = nmwTexteAffichage($s["description"] ?? "");
$lieuService = nmwTexteAffichage($s["lieu"] ?? t("place_to_confirm"));
$dateService = nmwDateAffichage($s["date_service"] ?? null);
$heureDebut = substr((string) ($s["heure_debut"] ?? ""), 0, 5);
$heureFin = substr((string) ($s["heure_fin"] ?? ""), 0, 5);
$capaciteMax = isset($s["capacite_max"]) && $s["capacite_max"] !== null
    ? (int) $s["capacite_max"]
    : null;

$nbInscrits = (int) ($s["nb_inscrits"] ?? 0);

$serviceComplet = $capaciteMax !== null &&
    $nbInscrits >= $capaciteMax;
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
    <?= htmlspecialchars($nomService) ?>
</h2>

<p class="nmw-service-description">
    <?= htmlspecialchars($descriptionService) ?>
</p>

<div class="nmw-service-meta">

<div class="nmw-meta-row">
    <i class="fa-solid fa-location-dot"></i>
    <span><?= htmlspecialchars($lieuService) ?></span>
</div>

<div class="nmw-meta-row">
    <i class="fa-regular fa-calendar"></i>
    <span><?= htmlspecialchars($dateService) ?></span>
</div>

<div class="nmw-meta-row">
    <i class="fa-regular fa-clock"></i>
    <span>
        <?= htmlspecialchars($heureDebut) ?>
        <?= $heureFin !== ""
            ? " – " . htmlspecialchars($heureFin)
            : ""
        ?>
    </span>
</div>

</div>

<?php if ($capaciteMax !== null): ?>

<div class="nmw-capacity">
    <i class="fa-solid fa-users"></i>
    <?= $nbInscrits ?> / <?= $capaciteMax ?>
    participant(s)
</div>

<?php endif; ?>

<?php if ($serviceComplet): ?>

<button
    type="button"
    class="nmw-service-action gray"
    disabled
>
    <i class="fa-solid fa-users-slash"></i>
    Service complet
</button>

<?php elseif (!$estConnecte): ?>

<a
    href="/login.php"
    class="nmw-service-action blue"
>
    <i class="fa-solid fa-right-to-bracket"></i>
    Se connecter pour participer
</a>

<?php elseif ($estBenevole): ?>

<form method="post" class="mt-auto">

<input
    type="hidden"
    name="action"
    value="inscription_service"
>

<input
    type="hidden"
    name="id_service"
    value="<?= $idService ?>"
>

<button
    type="submit"
    class="nmw-service-action"
    onclick="return confirm('Voulez-vous vous inscrire à ce service ?');"
>
    <i class="fa-solid fa-hand"></i>
    S'inscrire
</button>

</form>

<?php elseif ($estAdmin): ?>

<a
    href="/services/index.php"
    class="nmw-service-action gray"
>
    <i class="fa-solid fa-gear"></i>
    Gérer les services
</a>

<?php endif; ?>

</div>

</article>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>