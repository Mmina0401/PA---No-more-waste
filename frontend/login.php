<?php
session_start();
require_once __DIR__ . "/includes/api.php";

// Si déjà connecté, pas besoin de repasser par ici
if (isset($_SESSION["utilisateur"])) {
    header("Location: dashboard.php");
    exit;
}

$erreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reponse = API::post("/api/auth/login", [
        "email"        => trim($_POST["email"]),
        "mot_de_passe" => $_POST["mot_de_passe"],
    ]);

    if (isset($reponse["jeton"])) {
        $_SESSION["jeton"]       = $reponse["jeton"];
        $_SESSION["utilisateur"] = $reponse["utilisateur"];
        header("Location: dashboard.php");
        exit;
    }

    $erreur = "Email ou mot de passe incorrect.";
}

include __DIR__ . "/includes/header.php";
?>

<div class="d-flex align-items-center justify-content-center" style="min-height: 90vh;">
    <div class="card p-4 shadow-sm" style="width: 380px;">
        <h3 class="text-center mb-3 text-success">
            <i class="fa-solid fa-leaf"></i> No More Waste
        </h3>

        <?php if ($erreur): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="mot_de_passe" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Se connecter</button>
        </form>
    </div>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>