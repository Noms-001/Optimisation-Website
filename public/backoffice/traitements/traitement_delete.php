<?php
require_once '../../config/database.php';
require_once '../../commons/repositories/ArticleRepository.php';

$pdo = getDatabaseConnection();
$repo = new ArticleRepository($pdo);

if (isset($_GET['delete'])) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        $error = "Id Manquant.";
    }

    $deleted = $repo->delete($id);

    if ($deleted) {
        $sucess = "Article suprime avec succes.";
    } else {
        $error = "Erreur lors de la suppression de l'article";
    }
}
?>