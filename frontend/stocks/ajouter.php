<?php
session_start();

include "../includes/header.php";
include "../includes/navbar.php";
require_once "../includes/api.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

   $resultat = API::post("/api/stocks/create", [
    "id_produit"  => (int) $_POST["id_produit"],
    "quantite"    => (int) $_POST["quantite"],
    "emplacement" => trim($_POST["emplacement"])
]);

echo "<pre>";
var_dump($resultat);
echo "</pre>";
exit;
}


?>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-2 p-0">
            <?php include "../includes/sidebar.php"; ?>
        </div>

        <div class="col-md-10 p-4">

            <h2>Ajouter un stock</h2>

            <hr>

            <form method="post">

                <div class="mb-3">
                    <label class="form-label">ID Produit</label>
                    <input
                        type="number"
                        name="id_produit"
                        class="form-control"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantité</label>
                    <input
                        type="number"
                        name="quantite"
                        class="form-control"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Emplacement</label>
                    <input
                        type="text"
                        name="emplacement"
                        class="form-control"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-success">
                    Ajouter le stock
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Annuler
                </a>

            </form>

        </div>

    </div>

</div>

<?php include "../includes/footer.php"; ?>