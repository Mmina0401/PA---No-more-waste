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
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des bénévoles</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F4F9F3;
            margin: 0;
            padding: 40px;
            color: #263238;
        }

        h1 {
            color: #2E7D32;
        }

        .container {
            max-width: 1400px;
            margin: auto;
        }

        .message {
            background-color: #7BC96F;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .error {
            background-color: #F2C94C;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 14px;
            overflow: hidden;
        }

        th {
            background-color: #2E7D32;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background-color: #F4F9F3;
        }

        .actif {
            background-color: #7BC96F;
            padding: 5px 10px;
            border-radius: 10px;
        }

        .inactif {
            background-color: #F2C94C;
            padding: 5px 10px;
            border-radius: 10px;
        }

        .btn {
            background-color: #2B7A9B;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
        }

        dialog {
            border: none;
            border-radius: 16px;
            padding: 25px;
            width: 420px;
        }

        dialog::backdrop {
            background: rgba(0, 0, 0, 0.4);
        }

        .field {
            margin-bottom: 15px;
        }

        .field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .field input[type="text"],
        .field input[type="email"] {
            width: 100%;
            padding: 9px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .competence-item {
            margin: 7px 0;
        }

        .actions-dialog {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-save {
            background-color: #2E7D32;
            color: white;
            border: none;
            padding: 9px 14px;
            border-radius: 10px;
            cursor: pointer;
        }

        .btn-cancel {
            background-color: #ddd;
            border: none;
            padding: 9px 14px;
            border-radius: 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Gestion des bénévoles</h1>

    <?php if ($message): ?>
        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!is_array($benevoles)): ?>

        <p>Impossible de récupérer les bénévoles.</p>

    <?php else: ?>

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
                    <td><?= htmlspecialchars($benevole["telephone"] ?? "") ?></td>
                    <td><?= htmlspecialchars($benevole["ville"] ?? "") ?></td>

                    <td>
                        <?php if (!empty($benevole["actif"])): ?>
                            <span class="actif">Actif</span>
                        <?php else: ?>
                            <span class="inactif">En attente</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php
                        if (!empty($benevole["competences"])) {
                            echo htmlspecialchars(implode(", ", $benevole["competences"]));
                        } else {
                            echo "Aucune";
                        }
                        ?>
                    </td>

                    <td>
                        <button
                            class="btn"
                            onclick='ouvrirModification(<?= json_encode($benevole, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                        >
                            Modifier
                        </button>
                    </td>
                </tr>

            <?php endforeach; ?>

            </tbody>
        </table>

    <?php endif; ?>

</div>

<dialog id="editDialog">

    <h2>Modifier le bénévole</h2>

    <form method="POST">

        <input type="hidden" name="id_utilisateur" id="id_utilisateur">

        <div class="field">
            <label>Nom</label>
            <input type="text" name="nom" id="nom" required>
        </div>

        <div class="field">
            <label>Prénom</label>
            <input type="text" name="prenom" id="prenom" required>
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="field">
            <label>Téléphone</label>
            <input type="text" name="telephone" id="telephone">
        </div>

        <div class="field">
            <label>Ville</label>
            <input type="text" name="ville" id="ville">
        </div>

        <div class="field">
            <label>
                <input type="checkbox" name="actif" id="actif">
                Bénévole actif
            </label>
        </div>

        <div class="field">
            <label>Compétences</label>

            <?php foreach ($competencesDisponibles as $id => $nom): ?>

                <div class="competence-item">
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

        <div class="actions-dialog">

            <button
                type="button"
                class="btn-cancel"
                onclick="fermerModification()"
            >
                Annuler
            </button>

            <button
                type="submit"
                class="btn-save"
            >
                Enregistrer
            </button>

        </div>

    </form>

</dialog>

<script>
function ouvrirModification(benevole) {
    document.getElementById("id_utilisateur").value = benevole.id_utilisateur;
    document.getElementById("nom").value = benevole.nom ?? "";
    document.getElementById("prenom").value = benevole.prenom ?? "";
    document.getElementById("email").value = benevole.email ?? "";
    document.getElementById("telephone").value = benevole.telephone ?? "";
    document.getElementById("ville").value = benevole.ville ?? "";
    document.getElementById("actif").checked = benevole.actif === true;

    const checkboxes = document.querySelectorAll(
        '#editDialog input[name="competences[]"]'
    );

    checkboxes.forEach(function(checkbox) {
        checkbox.checked = false;

        if (benevole.competences?.includes(checkbox.dataset.nom)) {
            checkbox.checked = true;
        }
    });

    document.getElementById("editDialog").showModal();
}

function fermerModification() {
    document.getElementById("editDialog").close();
}
</script>

</body>
</html>