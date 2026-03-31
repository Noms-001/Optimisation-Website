<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../commons/models/Article.php';
require_once __DIR__ . '/../../commons/models/Auteur.php';
require_once __DIR__ . '/../../commons/models/Media.php';
require_once __DIR__ . '/../../commons/models/TypeArticle.php';
require_once __DIR__ . '/../../commons/repositories/ArticleRepository.php';
require_once __DIR__ . '/../../commons/repositories/TypeArticleRepository.php';
require_once __DIR__ . '/../repositories/AuteurRepository.php';

session_start();

$pdo = getDatabaseConnection();
$auteurRepo = new AuteurRepository($pdo);
$typeRepo = new TypeArticleRepository($pdo);

// Récupération des listes pour les selects
$auteurs = $auteurRepo->findAll();
$types = $typeRepo->findAll();

$article = null;
$seoRecommendations = null;

// Si un ID est passé en paramètre, on charge l'article pour modification
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $articleRepo = new ArticleRepository($pdo);
    $article = $articleRepo->findById($_GET['id']);
    
    if ($article) {
        // Calcul des recommandations SEO
        $seoRecommendations = $article->getScoreSEOGlobal();
    }
}

// Gestion des messages flash
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>