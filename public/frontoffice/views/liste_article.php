<?php
include '../../commons/traitements/traitement_liste_article.php';
include '../../commons/version.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horizon Info | Tous les articles</title>
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/fo-styles.css?ver=<?= $version ?>">
    <style>
        .article-content {
            display: flex;
            flex-direction: column;
        }
    </style>
</head>

<body>
    <?php include '../partials/navbar.php' ?>
    <div class="container">
        <!-- Barre de recherche -->
        <div class="search-section">
            <form action="/tous-nos-articles" method="get">
            <div class="search-bar">
                <input type="text" name="motcle" id="searchInput" class="search-input"
                    placeholder="Rechercher un article par titre, catégorie ou mot-clé...">
                <button class="search-btn">Rechercher</button>
                <button class="clear-search">Effacer</button>
            </div>
            </form>
        </div>

        <!-- En-tête des résultats -->
        <div class="results-header">
            <h2>Tous les articles</h2>
            <div class="results-count" id="resultsCount"></div>
        </div>

        <!-- Liste des articles -->
        <div id="articlesContainer" class="articles-list">
            <?php if (empty($articles)): ?>
                <div class="no-results">
                    <i class="no-results-icon" style="font-size: 48px; opacity: 0.5;"><i class="bi bi-mailbox"></i></i>
                    <p style="margin-top: 16px;">Aucun article ne correspond à votre recherche.</p>
                    <p style="font-size: 14px;">Essayez d'autres mots-clés ou consultez tous nos articles.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($articles as $article): ?>
                <a href="<?= $article->getUrl() ?>" style="text-decoration: none; color: inherit;">
                    <div class="article-row">
                        <div class="article-image">
                            <img src="../../uploads/<?php echo $article->srcThumb() ?>" alt="<?php echo $article->imageAlt ?>" style="height: 140px">
                        </div>
                        <div class="article-content">
                            <div class="article-category">
                                <?php
                                echo $article->type->libelle;
                                echo $article->priorite ? '<span class="featured-badge">À la une</span>' : '';
                                ?>
                            </div>
                            <div class="article-title">
                                <?php echo $article->titre ?>
                            </div>
                            <p class="article-excerpt"><?php echo $article->firstParagraph() ?></p>
                            <div class="article-meta">
                                <span><i class='bi bi-calendar2-week'></i> <?php echo $article->datePublication || 'Date non définie' ?></span>
                                <?php echo $article->motClePrincipal ? "<span><i class='bi bi-tag'></i>" . $article->motClePrincipal . "</span>" : '' ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>

        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="pagination"></div>
    </div>
    <?php include '../partials/footer.php' ?>
</body>

</html>