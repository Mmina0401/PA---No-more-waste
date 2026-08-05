<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">

<div class="container-fluid">

<a class="navbar-brand fw-bold" href="/dashboard.php">

<i class="fa-solid fa-leaf"></i>

No More Waste

</a>

<div class="text-white d-flex align-items-center gap-3">

<span>
<i class="fa-solid fa-user"></i>
<?= htmlspecialchars(trim(($_SESSION["utilisateur"]["prenom"] ?? "") . " " . ($_SESSION["utilisateur"]["nom"] ?? ""))) ?>
(<?= htmlspecialchars($_SESSION["utilisateur"]["role"] ?? "?") ?>)
</span>

<a href="/logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>

</div>

</div>

</nav>