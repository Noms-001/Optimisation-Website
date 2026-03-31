<?php
require_once __DIR__ . '/../repositories/ArticleRepository.php';
require_once __DIR__ . '/../repositories/TypeArticleRepository.php';
require_once __DIR__ . '/../../config/database.php';

$pdo = getDatabaseConnection();
$repo = new ArticleRepository($pdo);
$typeRepo = new TypeArticleRepository($pdo);

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$motcle = $_GET['motcle'] ?? null;
$categorie = $_GET['categorie'] ?? null;

$limit = 8;
$offset = ($page - 1) * $limit;

$articles = $repo->findAll(
    motCle: $motcle,
    categorie: $categorie,
    limit: $limit,
    offset: $offset
);

$totalArticles = $repo->countAll(
    motCle: $motcle,
    categorie: $categorie
);

$totalPages = ceil($totalArticles / $limit);

$types = $typeRepo->findAll();
?>