<?php
include '../traitements/traitement_delete.php';
include '../../commons/traitements/traitement_liste_article.php';
session_start();

if(!isset($_SESSION['user'])) {
    header("Location: /backoffice");
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Horizon Info | Administration</title>
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/bo-styles.css">
</head>

<body>
    <?php include '../partials/navbar.php' ?>

    <div class="toast-container">
        <?php if (isset($error)): ?>
            <div class="toast toast-error show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="toast-header">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong class="me-auto">Erreur</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fermer"></button>
                </div>
                <div class="toast-body">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="toast toast-success show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
                <div class="toast-header">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong class="me-auto">Succès</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fermer"></button>
                </div>
                <div class="toast-body">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-0" style="font-size: 1.9rem; letter-spacing: -0.02em;">Gestion des articles</h2>
                <p class="text-muted mt-1 mb-0"><i class="bi bi-database"></i> Contenus publiés · archivage local</p>
            </div>
            <a href="/backoffice/views/edition.php" class="btn-create"><i class="bi bi-file-earmark-plus"></i> Rédiger un article</a>
        </div>

        <div class="card-modern">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table-articles">
                    <thead>
                        <tr>
                            <th>Article & couverture</th>
                            <th>Catégorie</th>
                            <th>Mots-clés</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="articlesTableBody">
                        <?php if (empty($articles)) { ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="bi bi-journal-bookmark-fill"></i>
                                    <div style="font-weight: 500; margin-top: 8px;">Aucun article dans l'espace rédaction</div>
                                    <small class="text-muted">Créez votre premier article dès maintenant</small>
                                </td>
                            </tr>
                            <?php } else {
                            foreach ($articles as $article) { ?>

                                <tr class="fade-in">
                                    <td data-label="Article & couverture">
                                        <div class="d-flex align-items-center gap-3 flex-wrap flex-sm-nowrap">
                                            <img src="../../uploads/<?php echo $article->srcMini() ?>" class="cover-thumb" alt="<?php echo $article->imageAlt ?>" loading="lazy">
                                            <div class="title-wrapper">
                                                <strong><?php echo $article->titre ?></strong>
                                                <div class="meta-info">
                                                    <span><i class="bi bi-calendar2-week"></i> <?php echo $article->datePublication ?></span>
                                                    <?php echo $article->priorite ? '<span class="badge-une"><i class="bi bi-star-fill"></i> À la une</span>' : '' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Catégorie">
                                        <span class="badge-category"><i class="bi bi-tag"></i> <?= $article->type->libelle ?></span>
                                    </td>
                                    <td data-label="Mots-clés">
                                        <span class="keywords-text"><i class="bi bi-hash"></i><?= $article->motClePrincipal ?></span>
                                    </td>
                                    <td data-label="Actions" style="text-align: right;">
                                        <div class="action-icons">
                                            <a href="/backoffice/views/edition.php?id=<?= $article->id ?>" class="text-decoration-none" title="Modifier l'article">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="/backoffice/views/liste_article.php?id=<?= $article->id ?>&delete=on" class="text-decoration-none" title="Supprimer définitivement">
                                                <i class="bi bi-trash3"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>