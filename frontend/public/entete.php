<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>No More Waste</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
:root {
    --brume: #F4F9F3;
    --vert-foret: #2E7D32;
    --bleu-planete: #2B7A9B;
    --vert-feuille: #7BC96F;
    --bleu-ciel: #B9E3F3;
    --jaune-solaire: #F2C94C;
    --blanc: #FFFFFF;
    --anthracite: #263238;
}

body {
    background: var(--brume);
    color: var(--anthracite);
    font-family: 'Inter', sans-serif;
    font-size: 16px;
}

h1, h2, h3, .titre-nmw {
    font-family: 'Roboto Condensed', sans-serif;
    font-weight: 700;
    color: var(--vert-foret);
}

.texte-secondaire {
    color: #55636b;
    font-size: 14px;
}

.carte-nmw {
    background: var(--blanc);
    border-radius: 16px;
    border: none;
    box-shadow: 0 4px 16px rgba(38, 50, 56, 0.08);
}

.btn-principal {
    background: var(--vert-foret);
    border: none;
    border-radius: 14px;
    color: var(--blanc);
    font-weight: 600;
    padding: 10px 20px;
}
.btn-principal:hover { background: #245e28; color: var(--blanc); }

.btn-secondaire {
    background: var(--bleu-planete);
    border: none;
    border-radius: 14px;
    color: var(--blanc);
    font-weight: 600;
    padding: 10px 20px;
}
.btn-secondaire:hover { background: #1f5f78; color: var(--blanc); }

.navbar-nmw {
    background: var(--vert-foret) !important;
}
.navbar-nmw .nav-link,
.navbar-nmw .navbar-brand {
    color: var(--blanc) !important;
}

.badge-jaune {
    background: var(--jaune-solaire);
    color: var(--anthracite);
    font-weight: 600;
    border-radius: 999px;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-nmw shadow-sm">
<div class="container-fluid">
<a class="navbar-brand fw-bold" href="accueil.php">
<i class="fa-solid fa-leaf"></i> No More Waste
</a>
<div class="d-flex gap-2 flex-wrap">
<a href="services.php" class="btn btn-sm btn-outline-light">Services</a>
<a href="demande-collecte.php" class="btn btn-sm btn-outline-light">Demander une collecte</a>
<a href="candidature-benevole.php" class="btn btn-sm btn-outline-light">Devenir bénévole</a>
<a href="/login.php" class="btn btn-sm badge-jaune">Espace membre</a>
</div>
</div>
</nav>