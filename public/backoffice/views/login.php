<?php
include '../traitements/traitement_login.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Horizon Info | Connexion Rédaction</title>
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/bo-styles.css">
</head>

<body>

    <!-- Toast Container -->
    <div class="toast-container">
        <?php if (isset($error)): ?>
            <div class="toast toast-error show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="toast-header">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong class="me-auto">Erreur</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fermer"></button>
                </div>
                <div class="toast-body">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h2>Horizon<span>Info</span></h2>
                <p>Espace rédaction & administration</p>
            </div>

            <div class="login-body">
                <form id="loginForm" action="/backoffice/views/login.php" method="post">
                    <div class="input-group-custom">
                        <i class="bi bi-envelope-fill"></i>
                        <input type="email" id="email" name="email" value="admin@gmail.com" autocomplete="email" required>
                    </div>
                    <div class="input-group-custom">
                        <i class="bi bi-lock-fill"></i>
                        <input type="password" id="password" name="motDePasse" value="1234" autocomplete="current-password" required>
                    </div>

                    <div class="login-options">
                        <label class="remember-me">
                            <input type="checkbox" id="rememberMe"> Se souvenir de moi
                        </label>
                        <a href="#" class="forgot-link">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Se connecter
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <p><i class="bi bi-shield-check"></i> Connexion sécurisée · CMS Horizon Info v2.0</p>
            </div>
        </div>
    </div>
</body>

</html>