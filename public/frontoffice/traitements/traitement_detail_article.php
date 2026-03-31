<?php
// Dans traitement_detail_article.php
include '../../config/database.php';
include '../../commons/repositories/ArticleRepository.php';
include '../../commons/repositories/TypeArticleRepository.php';
include '../../commons/services/TypeArticleService.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception("Article introuvable");
    }

    $id = (int) $_GET['id'];
    $categorie = null;
    $pdo = getDatabaseConnection();
    
    $typeRepo = new TypeArticleRepository($pdo);
    $typeService = new TypeArticleService($typeRepo);
    $types = $typeService->getAll();
    
    $repo = new ArticleRepository($pdo);
    $article = $repo->findById($id);
    
    if (!$article) {
        throw new Exception("Article non trouvé");
    }
    
    $articlesLiees = $repo->findArticlesLiees($id);
    
    // Extraction du contenu avec conservation des paragraphes
    $extract = $article->extract();
    $blocks = $extract["blocks"];
    
    // Reconstruire le contenu en associant les médias aux bons paragraphes
    $contentWithMedia = [];
    $paragraphCounter = 0;
    
    // D'abord, créer un tableau des médias par numéro de paragraphe
    $mediaByParagraph = [];
    foreach ($article->media as $media) {
        $paragraphNum = $media->paragraphe ?? 1;
        if (!isset($mediaByParagraph[$paragraphNum])) {
            $mediaByParagraph[$paragraphNum] = [];
        }
        $mediaByParagraph[$paragraphNum][] = $media;
    }
    
    // Construire la structure finale
    foreach ($blocks as $block) {
        if ($block['type'] === 'p') {
            $paragraphCounter++;
            $contentWithMedia[] = [
                'type' => 'paragraph',
                'number' => $paragraphCounter,
                'content' => $block['text'],
                'media_before' => $mediaByParagraph[$paragraphCounter] ?? [],
                'media_after' => [] // Si vous voulez des images après le paragraphe
            ];
        } else {
            // Pour les titres, citations, etc.
            $contentWithMedia[] = [
                'type' => $block['type'],
                'content' => $block['text'],
                'media_before' => [],
                'media_after' => []
            ];
        }
    }
    
    $firstParagraph = $article->firstParagraph();
    
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

function getInitials($nom) {
    $words = explode(' ', trim($nom));
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    return $initials;
}
?>