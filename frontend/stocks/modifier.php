<?php
session_start();

include "../includes/header.php";
include "../includes/navbar.php";
require_once "../includes/api.php";

$id = (int)($_GET["id"] ?? 0);

$stock = null;

$stocks = API::get("/api/stocks");

foreach ($stocks as $s) {
    if ($s["id_stock"] == $id) {
        $stock = $s;
        break;
    }
}

if (!$stock) {
    die("Stock introuvable");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    API::put("/api/stocks/update", [

        "id_stock" => $id,
        "id_produit" => (int)$_POST["id_produit"],
        "quantite" => (int)$_POST["quantite"],
        "emplacement" => trim($_POST["emplacement"])

    ]);

    echo "<script>window.location='index.php';</script>";
    exit;
}
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">
<?php include "../includes/sidebar.php"; ?>
</div>

<div class="col-md-10 p-4">

<h2>Modifier un stock</h2>

<hr>

<form method="post">

<div class="mb-3">
<label class="form-label">ID Produit</label>

<input
type="number"
name="id_produit"
class="form-control"
value="<?= $stock["id_produit"] ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">Quantité</label>

<input
type="number"
name="quantite"
class="form-control"
value="<?= $stock["quantite"] ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">Emplacement</label>

<input
type="text"
name="emplacement"
class="form-control"
value="<?= htmlspecialchars($stock["emplacement"]) ?>"
required>

</div>

<button class="btn btn-success">
Enregistrer
</button>

<a href="index.php" class="btn btn-secondary">
Annuler
</a>

</form>

</div>

</div>

</div>

<?php include "../includes/footer.php"; ?>