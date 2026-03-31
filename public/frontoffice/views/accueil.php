<?php
include '../traitements/traitement_accueil.php';
include '../../commons/version.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horizon Info | Actualités Internationales</title>
    <link rel="stylesheet" href="../../assets/css/fo-styles.css?ver=<?= $version ?>">
</head>

<body>
    <?php include '../partials/navbar.php' ?>
    <div class="container">
        <!-- Section héroïque type "une" -->
        <div class="hero">
            <?php if (!empty($articlesUne)):
                $articleUne = $articlesUne[0]; // Premier article à la une
            ?>
                <a href="<?= $articleUne->getUrl() ?>" style="text-decoration: none; color: inherit;">
                    <div class="hero-main" style="background-image: url('../../uploads/<?php echo $articleUne->imageSrc ?>');">
                        <div class="category">
                            <?php
                            // Récupération du libellé du type
                            $typeLibelle = $articleUne->type->libelle;
                            echo htmlspecialchars($typeLibelle ?: 'À LA UNE');
                            ?>
                        </div>
                        <h2><?php echo htmlspecialchars($articleUne->titre); ?></h2>
                        <p><?php echo htmlspecialchars(substr(strip_tags($articleUne->firstParagraph()), 0, 150) . '...'); ?></p>
                        <div class="meta">
                            Par <?php
                                // Récupération du nom de l'auteur
                                $auteurNom = $articleUne->auteur->nom;
                                echo htmlspecialchars($auteurNom ?: 'Rédaction');
                                ?> · <?php echo date('d F Y', strtotime($articleUne->datePublication)); ?>
                        </div>
                    </div>
                </a>
            <?php endif; ?>

            <div class="hero-side">
                <?php foreach ($articlesLiees as $key => $articleLiee): ?>
                    <a href="<?= $articleLiee->getUrl() ?>" style="text-decoration: none; color: inherit;">
                        <div class="hero-card">
                            <div class="category">
                                <?php
                                $typeLibelle = $articleLiee->type->libelle;
                                echo htmlspecialchars($typeLibelle ?: 'ARTICLE');
                                ?>
                            </div>
                            <h3><?php echo htmlspecialchars($articleLiee->titre); ?></h3>
                            <p><?php echo htmlspecialchars(substr(strip_tags($articleLiee->firstParagraph()), 0, 100) . '...'); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Dernières actualités -->
        <div class="section-header">
            <h2>Dernières actualités</h2>
            <a href="/tous-nos-articles" class="section-link">Voir tout →</a>
        </div>
        <div class="articles-grid" id="latest-articles">
            <?php foreach ($dernieresActus as $article): ?>
                <a href="<?= $article->getUrl() ?>" style="text-decoration: none; color: inherit;">
                    <div class="article-card">
                        <div class="article-img">
                            <img src="../../uploads/<?php echo $article->srcThumb(); ?>" alt="<?php echo $article->imageAlt; ?>">
                        </div>
                        <div class="article-category">
                            <?php
                            $typeLibelle = $article->type->libelle;
                            echo htmlspecialchars($typeLibelle ?: 'ACTUALITÉ');
                            ?>
                        </div>
                        <h3><?php echo htmlspecialchars($article->titre); ?></h3>
                        <p><?php echo htmlspecialchars(substr(strip_tags($article->firstParagraph()), 0, 120) . '...'); ?></p>
                        <div class="meta">
                            Publié le <?php echo date('d F Y', strtotime($article->datePublication)); ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Section analyse et opinion -->
        <div class="two-column-layout">
            <div>
                <div class="section-header" style="margin-top:0">
                    <h2>Analyses & Décryptages</h2>
                </div>
                <?php foreach ($analysesRapports as $article): ?>
                    <a href="<?= $article->getUrl() ?>" style="text-decoration: none; color: inherit;">
                        <div class="analysis-card">
                            <div class="article-category">
                                <?php
                                $typeLibelle = $article->type->libelle;
                                echo htmlspecialchars($typeLibelle ?: 'ANALYSE');
                                ?>
                            </div>
                            <h3 style="font-size: 20px;"><?php echo htmlspecialchars($article->titre); ?></h3>
                            <p style="margin: 12px 0"><?php echo htmlspecialchars(substr(strip_tags($article->firstParagraph()), 0, 150) . '...'); ?></p>
                            <div class="meta">
                                Par <?php
                                    $auteurNom = $article->auteur->nom;
                                    echo htmlspecialchars($auteurNom ?: 'Rédaction');
                                    ?> · <?php echo date('d F Y', strtotime($article->datePublication)); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <div>
                <div class="section-header" style="margin-top:0">
                    <h2>Les plus lus</h2>
                </div>
                <ul style="list-style: none;">
                    <?php foreach ($articlesPlusLus as $index => $article): ?>
                        <a href="<?= $article->getUrl() ?>" style="text-decoration: none; color: inherit;">
                            <li style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 12px;">
                                <span style="font-weight: bold; color:#cc0000;"><?php echo $index + 1; ?>.</span>
                                <?php echo htmlspecialchars($article->titre); ?>
                            </li>
                        </a>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php include '../partials/footer.php' ?>
</body>

</html>