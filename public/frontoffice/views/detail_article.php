<?php
include '../traitements/traitement_detail_article.php';
include '../../commons/version.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($article->metaDescription ?? substr(strip_tags($firstParagraph), 0, 160)); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($article->motClePrincipal ?? ''); ?>">
    <title><?php echo htmlspecialchars($article->titre); ?> | Horizon Info</title>
    <link rel="stylesheet" href="../../assets/css/fo-styles.css?ver=<?= $version ?>">
    <style>
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 40px 24px;
            display: grid;
            grid-template-columns: 800px 1fr;
            gap: 60px;
        }

        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            h1.article-title {
                font-size: 32px;
            }
        }


        .author-initials {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #cc0000;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin-right: 12px;
        }
    </style>
</head>

<body>

    <?php include '../partials/navbar.php' ?>

    <div class="container">
        <main>
            <article>
                <header class="article-header">
                    <div style="display: flex; flex-direction: column">
                        <span class="category-tag">
                            <?php echo htmlspecialchars($article->type->libelle ?? 'Géopolitique'); ?>
                        </span>
                        <h1 class="article-title"><?php echo htmlspecialchars($article->titre); ?></h1>
                        <?php if (!empty($article->metaDescription)): ?>
                            <p class="article-excerpt"><?php echo htmlspecialchars($article->metaDescription); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="article-meta">
                        <div class="author-initials">
                            <?php echo htmlspecialchars(getInitials($article->auteur->nom ?? 'Auteur')); ?>
                        </div>
                        <div>
                            <strong>Par <?php echo htmlspecialchars($article->auteur->nom ?? 'Auteur'); ?></strong><br>
                            <span>Publié le <?php echo date('d F Y', strtotime($article->datePublication ?? 'now')); ?> ·
                                <?php
                                // Calcul approximatif du temps de lecture (200 mots par minute)
                                $wordCount = str_word_count(strip_tags($article->contenu ?? ''));
                                $readingTime = max(1, ceil($wordCount / 200));
                                echo $readingTime . ' min de lecture';
                                ?>
                            </span>
                        </div>
                    </div>
                </header>

                <?php if (!empty($article->imageSrc)): ?>
                    <div class="main-image">
                        <img src="../../uploads/<?php echo htmlspecialchars($article->imageSrc); ?>"
                            alt="<?php echo htmlspecialchars($article->imageAlt ?? 'Image de couverture'); ?>"
                            style="width:100%; height:auto; border-radius:8px;">
                    </div>
                <?php endif; ?>

                <div class="article-body clearfix">
                    <?php foreach ($contentWithMedia as $item): ?>

                        <?php
                        // Gestion des images AVANT l'élément (pour les paragraphes)
                        if ($item['type'] === 'paragraph' && !empty($item['media_before'])) {
                            foreach ($item['media_before'] as $media) { ?>
                                <div style="height: 130px; margin-top: 30px;">
                                    <figure class="article-image image-left">
                                        <img src="../../uploads/<?php echo htmlspecialchars($media->src); ?>"
                                            alt="<?php echo htmlspecialchars($media->alt ?? 'Image d\'illustration'); ?>"
                                            loading="lazy">
                                        <?php if (!empty($media->alt)): ?>
                                            <figcaption><?php echo htmlspecialchars($media->alt); ?></figcaption>
                                        <?php endif; ?>
                                    </figure>
                                    <p><?php echo htmlspecialchars($item['content']); ?></p>
                                </div>
                            <?php }
                        } else { ?>
                            <?php switch ($item['type']):
                                case 'paragraph': ?>
                                    <p><?php echo htmlspecialchars($item['content']); ?></p>
                                <?php break;

                                case 'h2': ?>
                                    <h2><?php echo htmlspecialchars($item['content']); ?></h2>
                                <?php break;

                                case 'h3': ?>
                                    <h3><?php echo htmlspecialchars($item['content']); ?></h3>
                                <?php break;

                                case 'h4': ?>
                                    <h4><?php echo htmlspecialchars($item['content']); ?></h4>
                                <?php break;

                                case 'h5': ?>
                                    <h5><?php echo htmlspecialchars($item['content']); ?></h5>
                                <?php break;

                                case 'h6': ?>
                                    <h6><?php echo htmlspecialchars($item['content']); ?></h6>
                                <?php break;

                                case 'blockquote': ?>
                                    <blockquote><?php echo htmlspecialchars($item['content']); ?></blockquote>
                            <?php break;
                            endswitch; ?>
                    <?php }
                    endforeach; ?>
                </div>
            </article>
        </main>

        <aside>
            <div class="sidebar-section">
                <h3 class="sidebar-title">À lire aussi</h3>

                <?php foreach ($articlesLiees as $articleLie): ?>
                    <a href="<?= $articleLie->getUrl() ?>" style="text-decoration: none; color: inherit;">
                        <div class="related-card">
                            <span class="category-tag" style="font-size: 11px;">
                                <?php echo htmlspecialchars($articleLie->type->libelle ?? 'Article'); ?>
                            </span>
                            <h4>
                                <?php echo htmlspecialchars($articleLie->titre); ?>
                            </h4>
                            <p style="font-size: 13px; color: #666; margin-top: 8px;">
                                <?php echo htmlspecialchars(substr(strip_tags($articleLie->firstParagraph()), 0, 120) . '...'); ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>

                <?php if (empty($articlesLiees)): ?>
                    <p>Aucun article lié pour le moment.</p>
                <?php endif; ?>
            </div>

            <div class="sidebar-section"
                style="margin-top: 40px; background: #f9f9f9; padding: 20px; border-radius: 12px;">
                <h3 style="font-size: 18px; margin-bottom: 12px;">Newsletter</h3>
                <p style="font-size: 14px; margin-bottom: 16px;">Recevez chaque matin l'essentiel de l'actualité
                    internationale.</p>
                <input type="email" placeholder="Votre email"
                    style="width:100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <button
                    style="width:100%; padding: 10px; background: #cc0000; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">S'abonner</button>
            </div>
        </aside>
    </div>

    <?php include '../partials/footer.php' ?>

</body>

</html>