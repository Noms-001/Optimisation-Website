<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../commons/models/Article.php';
require_once __DIR__ . '/../../commons/models/Auteur.php';
require_once __DIR__ . '/../../commons/models/Media.php';
require_once __DIR__ . '/../../commons/models/TypeArticle.php';
require_once __DIR__ . '/../../commons/repositories/ArticleRepository.php';
require_once __DIR__ . '/../../commons/services/ImageService.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données du formulaire
    $titre = $_POST['titre'] ?? '';
    $metaDescription = $_POST['meta_description'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    $motClePrincipal = $_POST['mot_cle_principal'] ?? '';
    $motCleSecondaire = $_POST['mot_cle_secondaire'] ?? '';
    $priorite = isset($_POST['priorite']) ? 1 : 0;
    $imageAlt = $_POST['image_alt'] ?? '';
    $idAuteur = $_POST['id_auteur'] ?? null;
    $idType = $_POST['id_type'] ?? null;
    $datePublication = date('Y-m-d H:i:s');

    // Gestion des médias (images de galerie)
    $mediaFiles = [];
    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['tmp_name'][0])) {
        $fileCount = count($_FILES['gallery_images']['tmp_name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                $mediaFiles[] = [
                    'file' => [
                        'name' => $_FILES['gallery_images']['name'][$i],
                        'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                        'type' => $_FILES['gallery_images']['type'][$i],
                        'error' => $_FILES['gallery_images']['error'][$i],
                        'size' => $_FILES['gallery_images']['size'][$i]
                    ],
                    'alt' => $_POST['gallery_alt'][$i] ?? '',
                    'paragraphe' => $_POST['gallery_paragraphe'][$i] ?? 1
                ];
            }
        }
    }

    // Création de l'objet Article
    $article = new Article([
        'titre' => $titre,
        'metaDescription' => $metaDescription,
        'contenu' => $contenu,
        'motClePrincipal' => $motClePrincipal,
        'motCleSecondaire' => $motCleSecondaire,
        'priorite' => $priorite,
        'imageAlt' => $imageAlt,
        'auteur' => $idAuteur,
        'type' => $idType,
        'datePublication' => $datePublication
    ]);

    // Si c'est une modification, on récupère l'ID
    if (!empty($_POST['id_article'])) {
        $article->id = $_POST['id_article'];
    }

    // Gestion du fichier de couverture
    $coverFile = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $coverFile = $_FILES['cover_image'];
    } else {
        $article->imageSrc = $_POST['existing_cover'] ?? null;
    }


    // Sauvegarde dans la base de données
    $pdo = getDatabaseConnection();
    $articleRepo = new ArticleRepository($pdo);

    try {
        $savedArticle = $articleRepo->save($article, $coverFile, $mediaFiles);
        var_dump($savedArticle);
        $_SESSION['success_message'] = "Article " . ($_POST['id_article'] ? "modifié" : "publié") . " avec succès !";
        header("Location: ../views/admin/edition.php?id=" . $savedArticle->id);
        exit();
    } catch (Exception $e) {
        echo $e;
        $_SESSION['error_message'] = "Erreur lors de la sauvegarde : " . $e->getMessage();
        header("Location: ../views/admin/edition.php");
        exit();
    }
}
