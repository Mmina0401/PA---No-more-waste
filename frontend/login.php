<?php
session_start();
require_once __DIR__ . "/includes/api.php";

function destinationApresConnexion($role)
{
    if ($role === "BENEVOLE") {
        return "/benevole/dashboard.php";
    }

    return "/dashboard.php";
}

// Si déjà connecté, rediriger vers l'espace correspondant au rôle.
if (isset($_SESSION["utilisateur"])) {
    header("Location: " . destinationApresConnexion($_SESSION["utilisateur"]["role"] ?? ""));
    exit;
}

$erreur = null;
$attenteValidation = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reponse = API::post("/api/auth/login", [
        "email"        => trim($_POST["email"]),
        "mot_de_passe" => $_POST["mot_de_passe"],
    ]);

    if (isset($reponse["jeton"])) {
        $_SESSION["jeton"]       = $reponse["jeton"];
        $_SESSION["utilisateur"] = $reponse["utilisateur"];

        header("Location: " . destinationApresConnexion($reponse["utilisateur"]["role"] ?? ""));
        exit;
    }

    if (($reponse["error"] ?? "") === "COMPTE_EN_ATTENTE") {
        $attenteValidation = $reponse["message"] ?? "Votre candidature est en attente de validation par un responsable.";
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}

include __DIR__ . "/includes/header.php";
?>

<style>
.login-page {
    min-height: 90vh;
    background:
        radial-gradient(circle at 12% 20%, rgba(123, 201, 111, .18), transparent 28%),
        radial-gradient(circle at 88% 75%, rgba(185, 227, 243, .28), transparent 30%),
        #F4F9F3;
}
.login-card {
    width: min(430px, calc(100% - 32px));
    border: 0;
    border-radius: 20px;
    box-shadow: 0 18px 50px rgba(38, 50, 56, .12);
}
.login-logo {
    color: #2E7D32;
    font-weight: 800;
}
.login-card .form-control {
    min-height: 46px;
    border-radius: 12px;
}
.login-card .btn-success {
    background: #2E7D32;
    border-color: #2E7D32;
    border-radius: 12px;
    min-height: 46px;
    font-weight: 700;
}
</style>

<div class="login-page d-flex align-items-center justify-content-center py-5">
    <div class="card login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="login-logo fs-3 mb-2">
                <i class="fa-solid fa-leaf"></i> No More Waste
            </div>
            <p class="text-muted mb-0">Connectez-vous à votre espace membre.</p>
        </div>

        <?php if ($attenteValidation): ?>
            <div class="alert alert-warning border-0" role="alert">
                <div class="d-flex gap-3">
                    <i class="fa-solid fa-hourglass-half mt-1"></i>
                    <div>
                        <strong>Candidature en attente de validation</strong>
                        <div class="mt-1"><?= htmlspecialchars($attenteValidation) ?></div>
                        <small class="d-block mt-2">Vous pourrez vous connecter avec ce même email et ce même mot de passe dès qu'un responsable aura validé votre compte.</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>

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

        <div class="text-center mt-4 small">
            <a href="/public/accueil.php" class="text-decoration-none">← Retour à l'accueil</a>
            <span class="mx-2 text-muted">•</span>
            <a href="/public/candidature-benevole.php" class="text-decoration-none">Devenir bénévole</a>
        </div>
    </div>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>