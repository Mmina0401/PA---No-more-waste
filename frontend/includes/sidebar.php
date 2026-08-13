<?php
$elementsMenu = [
    ["href" => "../dashboard.php",          "icone" => "fa-house",          "texte" => "Dashboard"],
    ["href" => "../commercant/index.php",  "icone" => "fa-shop",           "texte" => "Commerçants"],
    ["href" => "../candidatures/index.php", "icone" => "fa-user-plus",      "texte" => "Candidatures"],
    ["href" => "/benevole/gestion.php",      "icone" => "fa-people-group",  "texte" => "Bénévoles"],
    ["href" => "../collectes/index.php",    "icone" => "fa-truck",          "texte" => "Collectes"],
    ["href" => "../tournees/index.php",     "icone" => "fa-route",          "texte" => "Tournées"],
    ["href" => "../services/index.php",     "icone" => "fa-calendar-days",  "texte" => "Services"],
    ["href" => "../stocks/index.php",       "icone" => "fa-box",            "texte" => "Stocks"],
    ["href" => "../rappels/index.php",      "icone" => "fa-bell",           "texte" => "Rappels"],
    ["href" => "/planning/export.php", "icone" => "fa-file-excel", "texte" => "Export Excel"],
];
?>

<div class="bg-dark text-white vh-100 p-3">

<h4 class="mb-4">
<i class="fa-solid fa-seedling"></i>
Menu
</h4>

<ul class="nav flex-column">

<?php foreach ($elementsMenu as $element): ?>
<li class="nav-item mb-2">
<a class="nav-link text-white" href="<?= $element["href"] ?>">
<i class="fa-solid <?= $element["icone"] ?>"></i>
<?= $element["texte"] ?>
</a>
</li>
<?php endforeach; ?>

</ul>

</div>