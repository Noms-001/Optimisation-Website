<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../commons/models/Article.php';
require_once __DIR__ . '/../../commons/models/Media.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Créer un article temporaire pour l'analyse SEO
    $article = new Article([
        'titre' => $_POST['titre'] ?? '',
        'metaDescription' => $_POST['meta_description'] ?? '',
        'contenu' => $_POST['contenu'] ?? '',
        'motClePrincipal' => $_POST['mot_cle_principal'] ?? '',
        'motCleSecondaire' => $_POST['mot_cle_secondaire'] ?? '',
        'imageAlt' => $_POST['image_alt'] ?? ''
    ]);
    
    // Ajouter les médias temporaires
    $galleryCount = $_POST['gallery_count'] ?? 0;
    $mediaList = [];
    
    for ($i = 0; $i < $galleryCount; $i++) {
        $alt = $_POST["gallery_alt_$i"] ?? '';
        $media = new Media([
            'alt' => $alt
        ]);
        $mediaList[] = $media;
    }
    
    $article->media = $mediaList;
    
    // Récupérer les recommandations SEO
    $seoScore = $article->getScoreSEOGlobal();
    
    header('Content-Type: application/json');
    echo json_encode($seoScore);
    exit();
}
?>