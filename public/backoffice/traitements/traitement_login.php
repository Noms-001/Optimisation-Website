<?php

session_start();

include '../services/UserService.php';
include '../../config/database.php';

$pdo = getDatabaseConnection();
$repository = new UserRepository($pdo);
$service = new UserService($repository);

$error = null;

if (isset($_SESSION['user'])) {
    header("Location: /backoffice/liste-article");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['motDePasse'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {

        $user = $service->authentification($email, $password);
        error_log($password);

        if ($user) {
            // SESSION USER
            $_SESSION['user'] = [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ?? 'USER'
            ];
            error_log($password);

            // Redirection après login
            header("Location: ../views/liste_article.php");
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
            error_log($error);
        }
    }
}
