<?php

session_start();

require_once "../includes/auth.php";
exigerRole("ADMIN", "RESPONSABLE");

require_once "../includes/api.php";

$stocks = API::get("/api/stocks");

if (!is_array($stocks)) {
    $stocks = [];
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

            <h2>Gestion des Stocks</h2>
            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">

                <a href="ajouter.php" class="btn btn-success">
                    + Nouveau Stock
                </a>

                <input
                    type="search"
                    id="rechercheStock"
                    class="form-control"
                    style="max-width: 420px;"
                    placeholder="Rechercher un produit"
                >

            </div>

            <?php if (empty($stocks)): ?>

                <div class="alert alert-light border">
                    Aucun stock enregistré.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-striped"
                        id="tableStocks"
                    >

                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Produit</th>
                                <th>Code-barres</th>
                                <th>Catégorie</th>
                                <th>Quantité</th>
                                <th>Emplacement</th>
                                <th>Date d'entrée</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($stocks as $stock): ?>

                            <tr class="ligne-stock">

                                <td>
                                    <?= (int) $stock["id_stock"] ?>
                                </td>

                                <td class="nom-produit">
                                    <?= htmlspecialchars(
                                        $stock["nom_produit"] ?? ""
                                    ) ?>
                                </td>

                                <td class="code-barre">
                                    <?= htmlspecialchars(
                                        $stock["code_barre"] ?? ""
                                    ) ?>
                                </td>

                                <td class="categorie-produit">
                                    <?= htmlspecialchars(
                                        $stock["categorie"] ?? ""
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $stock["quantite"] ?? ""
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $stock["emplacement"] ?? ""
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        substr(
                                            $stock["date_entree"] ?? "",
                                            0,
                                            10
                                        )
                                    ) ?>
                                </td>

                                <td class="text-nowrap">

                                    <a
                                        href="modifier.php?id=<?= (int) $stock["id_stock"] ?>"
                                        class="btn btn-warning btn-sm"
                                    >
                                        Modifier
                                    </a>

                                    <a
                                        href="supprimer.php?id=<?= (int) $stock["id_stock"] ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Supprimer ce stock ?');"
                                    >
                                        Supprimer
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div
                    id="aucunResultat"
                    class="alert alert-warning d-none"
                >
                    Aucun stock ne correspond à la recherche.
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script>
const champRecherche = document.getElementById("rechercheStock");
const lignesStocks = document.querySelectorAll(".ligne-stock");
const aucunResultat = document.getElementById("aucunResultat");

if (champRecherche) {
    champRecherche.addEventListener("input", function () {
        const recherche = this.value
            .toLowerCase()
            .trim();

        let nombreResultats = 0;

        lignesStocks.forEach(function (ligne) {
            const nom = ligne
                .querySelector(".nom-produit")
                .textContent
                .toLowerCase();

            const categorie = ligne
                .querySelector(".categorie-produit")
                .textContent
                .toLowerCase();

            const codeBarre = ligne
                .querySelector(".code-barre")
                .textContent
                .toLowerCase();

            const correspond =
                nom.includes(recherche) ||
                categorie.includes(recherche) ||
                codeBarre.includes(recherche);

            ligne.style.display = correspond ? "" : "none";

            if (correspond) {
                nombreResultats++;
            }
        });

        if (aucunResultat) {
            aucunResultat.classList.toggle(
                "d-none",
                nombreResultats !== 0
            );
        }
    });
}
</script>

<?php include "../includes/footer.php"; ?>