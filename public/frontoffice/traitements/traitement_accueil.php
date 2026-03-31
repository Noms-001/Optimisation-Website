<?php

include '../../config/database.php';
include '../../commons/services/ArticleService.php';
include '../../commons/services/TypeArticleService.php';

$pdo = getDatabaseConnection();
$articleRepo = new ArticleRepository($pdo);
$articleService = new ArticleService($articleRepo);
$typeRepo = new TypeArticleRepository($pdo);
$typeService = new TypeArticleService($typeRepo);
$categorie = $_GET['categorie'] ?? null;

try {
    $articlesUne = $articleRepo->getAlaUne(1);

    $dernieresActus = $articleService->getArticlesRecentes(3);

    $analysesRapports = $articleService->getArticlesAnalysesRapports(2);

    $articlesPlusLus = $articleService->getArticlesPlusLues(3);

    $types = $typeService->getAll();

    $articlesLiees = [];
    if (!empty($articlesUne)) {
        $firstUne = reset($articlesUne);
        $articlesLiees = $articleService->getArticlesLiees($firstUne->id);
    }
} catch (Exception $e) {
    $articlesUne = [];
    $dernieresActus = [];
    $analysesRapports = [];
    $articlesPlusLus = [];
    $types = [];
    $articlesLiees = [];
    error_log("Erreur dans traitement_accueil: " . $e->getMessage());
}
