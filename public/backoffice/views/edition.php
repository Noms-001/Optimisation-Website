<?php
include '../traitements/traitement_edition.php';
include '../traitements/traitement_insert.php';

if(!isset($_SESSION['user'])) {
    header("Location: /backoffice");
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horizon Info | <?php echo $article ? 'Modification' : 'Nouvel'; ?> article</title>
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/bo-styles.css">
    <!-- TinyMCE -->
    <script src="../../assets/tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
</head>

<body>
    <?php include '../partials/navbar.php' ?>
    <div class="admin-container">
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-semibold" style="font-size: 26px;">
                <?php echo $article ? 'Modifier l\'article' : 'Nouvel article'; ?>
            </h2>
            <div class="d-flex gap-2">
                <button class="btn-outline-custom" onclick="history.back()">
                    <i class="bi bi-x-lg"></i> Annuler
                </button>
                <button class="btn-primary-custom" onclick="validateAndShowSEO()">
                    <i class="bi bi-cloud-upload"></i> <?php echo $article ? 'Mettre à jour' : 'Publier'; ?>
                </button>
            </div>
        </div>

        <form id="articleForm" method="POST" action="edition.php" enctype="multipart/form-data">
            <?php if ($article && $article->id): ?>
                <input type="hidden" name="id_article" value="<?php echo $article->id; ?>">
            <?php endif; ?>

            <div class="row g-4">
                <!-- Colonne principale -->
                <div class="col-lg-8 sticky-side">
                    <div class="card-modern">
                        <label class="fw-semibold mb-2">Titre de l'article</label>
                        <input type="text" name="titre" id="artTitle" class="form-control form-control-lg mb-4"
                            placeholder="Titre de l'article"
                            value="<?php echo htmlspecialchars($article ? $article->titre : ''); ?>"
                            required>

                        <!-- Meta Title (SEO) -->
                        <div class="meta-section mb-4">
                            <label class="fw-semibold mb-2">
                                Meta Description
                                <span class="text-muted small">(Pour les moteurs de recherche)</span>
                            </label>
                            <input type="text" name="meta_description" id="metaTitle" class="form-control"
                                value="<?php echo htmlspecialchars($article ? $article->metaDescription : ''); ?>">
                            <div class="text-muted small mt-1">Apparaît dans les résultats de recherche. Si vide, le titre de l'article sera utilisé.</div>
                        </div>

                        <label class="fw-semibold mb-2">Contenu de l'article</label>
                        <textarea id="artContent" name="contenu" class="form-control" rows="14"
                            placeholder="Rédigez l'article..."><?php echo htmlspecialchars($article ? $article->contenu : ''); ?></textarea>
                    </div>
                </div>

                <!-- Colonne droite -->
                <div class="col-lg-4">
                    <!-- Auteur -->
                    <div class="card-modern mb-4">
                        <h5 class="fw-semibold mb-3"><i class="bi bi-person-circle"></i> Auteur</h5>
                        <div class="author-selector">
                            <label class="fw-semibold mb-2">Auteur de l'article <span class="text-danger">*</span></label>
                            <select name="id_auteur" id="authorSelect" class="form-select" required>
                                <option value="">Sélectionner un auteur</option>
                                <?php foreach ($auteurs as $auteur): ?>
                                    <option value="<?php echo $auteur->id; ?>"
                                        <?php echo ($article && $article->auteur && $article->auteur->id == $auteur->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($auteur->nom . ' - ' . $auteur->role); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Paramètres -->
                    <div class="card-modern mb-4">
                        <h5 class="fw-semibold mb-3"><i class="bi bi-sliders2"></i> Paramètres</h5>
                        <label class="fw-semibold mb-2">Catégorie</label>
                        <select name="id_type" id="artCategory" class="form-select mb-3" required>
                            <option value="">Sélectionner une catégorie</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?php echo $type->id; ?>"
                                    <?php echo ($article && $article->type && $article->type->id == $type->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type->libelle); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label class="fw-semibold mb-2">Mots-clés principaux</label>
                        <input type="text" name="mot_cle_principal" class="form-control mb-3"
                            placeholder="diplomatie, sécurité, analyse"
                            value="<?php echo htmlspecialchars($article ? $article->motClePrincipal : ''); ?>">

                        <label class="fw-semibold mb-2">Mots-clés secondaires</label>
                        <input type="text" name="mot_cle_secondaire" class="form-control mb-3"
                            placeholder="rapport, paix"
                            value="<?php echo htmlspecialchars($article ? $article->motCleSecondaire : ''); ?>">

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="priorite" id="artFeatured" value="1"
                                <?php echo ($article && $article->priorite == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-semibold">Article à la une</label>
                        </div>
                        <div class="text-muted small mt-2">Priorité en page d'accueil</div>
                    </div>

                    <!-- Couverture avec ALT -->
                    <div class="card-modern mb-4">
                        <h5 class="fw-semibold mb-3"><i class="bi bi-image"></i> Image de couverture</h5>
                        <?php if ($article && $article->imageSrc): ?>
                            <img id="coverPreview" class="cover-preview" src="../../uploads/<?php echo $article->srcThumb(); ?>" style="width: 100%; margin-bottom: 10px;">
                            <input type="file" name="cover_image" id="coverInput" class="form-control" accept="image/*" onchange="previewCover(this)">
                            <?php if ($article && $article->imageSrc): ?>
                                <input type="hidden" name="existing_cover" value="<?php echo $article->imageSrc; ?>">
                            <?php endif; ?>
                        <?php else: ?>
                            <input type="file" name="cover_image" id="coverInput" class="form-control" accept="image/*" onchange="previewCover(this)">
                            <img id="coverPreview" class="cover-preview">
                        <?php endif; ?>
                        <div class="mt-2">
                            <label class="small fw-semibold">Alt text (SEO) :</label>
                            <input type="text" name="image_alt" id="coverAlt" class="form-control form-control-sm mt-1"
                                placeholder="Description de l'image de couverture"
                                value="<?php echo htmlspecialchars($article ? $article->imageAlt : ''); ?>">
                            <div class="text-muted small mt-1">Améliore l'accessibilité et le référencement</div>
                        </div>
                    </div>

                    <!-- Galerie média avec ALT -->
                    <div class="card-modern">
                        <h5 class="fw-semibold mb-3"><i class="bi bi-collection"></i> Galerie média</h5>
                        <div class="upload-zone" onclick="document.getElementById('galleryInput').click()">
                            <i class="bi bi-plus-circle fs-4"></i>
                            <p class="mb-0 mt-2 small text-muted">Ajouter des images</p>
                        </div>
                        <input type="file" id="galleryInput" class="d-none" accept="image/*" multiple onchange="prepareGalleryImages(this)">
                        <div id="galleryGrid" class="gallery-grid mt-3"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- MODAL SEO -->
    <div id="seoModal" class="modal-seo">
        <div class="modal-content-seo">
            <div class="modal-header-seo">
                <h3>Recommandations d'optimisation SEO</h3>
                <button class="btn-close" onclick="closeSeoModal()" style="position: absolute; right: 24px; top: 24px;"></button>
            </div>
            <div class="modal-body-seo">
                <div class="seo-score">
                    <div class="score-circle">
                        <div class="score-inner" id="seoScoreValue">0</div>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-semibold">Score global</h5>
                        <small class="text-muted">Basé sur les critères SEO</small>
                    </div>
                </div>

                <ul class="seo-checklist" id="seoChecklist">
                    <!-- Les recommandations seront insérées dynamiquement -->
                </ul>
            </div>
            <div class="modal-footer-seo">
                <button class="btn-outline-custom" onclick="closeSeoModal()">Fermer</button>
                <button class="btn-primary-custom" onclick="submitForm()"><?php echo $article ? 'Mettre à jour' : 'Publier'; ?></button>
            </div>
        </div>
    </div>

    <script>
        let galleryImagesData = [];
        <?php if ($article && $article->media): ?>
            // Charger les médias existants
            galleryImagesData = <?php echo json_encode(array_map(function ($media) {
                                    return [
                                        'src' => $media->src,
                                        'alt' => $media->alt,
                                        'paragraphe' => $media->paragraphe ?? 1,
                                        'isExisting' => true,
                                        'id' => $media->id
                                    ];
                                }, $article->media)); ?>;
            renderGallery();
        <?php endif; ?>

        // Configuration de TinyMCE
        tinymce.init({
            selector: '#artContent',
            license_key: 'gpl',
            plugins: 'lists link',

            toolbar: 'undo redo | styles | bold italic | bullist numlist | link',

            style_formats: [{
                    title: 'Paragraphe',
                    block: 'p'
                },
                {
                    title: 'Titre 2',
                    block: 'h2'
                },
                {
                    title: 'Titre 3',
                    block: 'h3'
                },
                {
                    title: 'Titre 4',
                    block: 'h4'
                },
                {
                    title: 'Titre 5',
                    block: 'h5'
                },
                {
                    title: 'Titre 6',
                    block: 'h6'
                },
                {
                    title: 'Citation',
                    block: 'blockquote'
                }
            ],

            block_formats: 'Paragraph=p; Heading',

            valid_elements: 'p,h2,h3,blockquote,a[href|target],strong,em,i',

            forced_root_block: 'p',

            paste_as_text: true,

            setup: function(editor) {
                editor.on('change', function() {
                    updateSeoScoreFromServer();
                });
            }
        });

        function previewCover(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('coverPreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function prepareGalleryImages(input) {
            const files = Array.from(input.files);
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        galleryImagesData.push({
                            src: ev.target.result,
                            alt: '',
                            paragraphe: 1,
                            file: file,
                            isExisting: false
                        });
                        renderGallery();
                    };
                    reader.readAsDataURL(file);
                }
            });
            input.value = '';
        }

        function renderGallery() {
            const container = document.getElementById('galleryGrid');
            if (!container) return;

            if (galleryImagesData.length === 0) {
                container.innerHTML = '<div class="text-muted text-center small py-3">Aucune image</div>';
                return;
            }

            container.innerHTML = galleryImagesData.map((img, idx) => `
                <div class="gallery-item" style="position: relative; border-radius: 12px; overflow: hidden; background: #f8f9fa; margin-bottom: 12px;">
                    <img src="../../uploads/${img.src}" alt="${img.alt || 'Image sans description'}" style="width: 100%; height: 120px; object-fit: cover;">
                    <div class="btn-remove" onclick="removeGalleryItem(${idx})" style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.6); color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 2;">
                        <i class="bi bi-x"></i>
                    </div>
                    <div style="position: absolute; bottom: 8px; left: 8px; right: 8px; background: rgba(0,0,0,0.7); padding: 8px; border-radius: 6px; z-index: 2;">
                        <input type="text" placeholder="Description ALT" value="${escapeHtml(img.alt)}" 
                               onchange="updateAlt(${idx}, this.value)" 
                               style="width: 100%; margin-bottom: 5px; padding: 4px; font-size: 11px; border-radius: 4px; border: none;">
                        <input type="number" placeholder="Paragraphe" value="${img.paragraphe}" 
                               onchange="updateParagraphe(${idx}, this.value)" 
                               style="width: 100%; padding: 4px; font-size: 11px; border-radius: 4px; border: none;">
                    </div>
                </div>
            `).join('');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function updateAlt(index, value) {
            galleryImagesData[index].alt = value;
        }

        function updateParagraphe(index, value) {
            galleryImagesData[index].paragraphe = parseInt(value) || 1;
        }

        function removeGalleryItem(index) {
            if (confirm('Supprimer cette image de la galerie ?')) {
                galleryImagesData.splice(index, 1);
                renderGallery();
            }
        }

        async function updateSeoScoreFromServer() {
            const formData = new FormData();
            formData.append('titre', document.getElementById('artTitle').value);
            formData.append('meta_description', document.getElementById('metaTitle').value);
            formData.append('contenu', tinymce.get('artContent').getContent());
            formData.append('mot_cle_principal', document.querySelector('input[name="mot_cle_principal"]').value);
            formData.append('mot_cle_secondaire', document.querySelector('input[name="mot_cle_secondaire"]').value);
            formData.append('image_alt', document.getElementById('coverAlt').value);

            // Ajouter les infos des médias
            formData.append('gallery_count', galleryImagesData.length);
            galleryImagesData.forEach((img, idx) => {
                formData.append(`gallery_alt_${idx}`, img.alt);
                formData.append(`gallery_paragraphe_${idx}`, img.paragraphe);
            });

            try {
                const response = await fetch('../traitements/ajax_seo_check.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                displaySeoModal(data);
            } catch (error) {
                console.error('Erreur SEO:', error);
            }
        }

        function displaySeoModal(seoData) {
            document.getElementById('seoScoreValue').textContent = seoData.score;

            const checklist = document.getElementById('seoChecklist');
            checklist.innerHTML = seoData.messages.map(msg => `
                <li>
                    <i class="bi ${msg.includes('optimisé') ? 'bi-check-circle-fill check-good' : 'bi-exclamation-triangle-fill check-warning'}"></i>
                    <span>${escapeHtml(msg)}</span>
                </li>
            `).join('');
        }

        function validateAndShowSEO() {
            // Vérifier les champs obligatoires
            const titre = document.getElementById('artTitle').value;
            const auteur = document.getElementById('authorSelect').value;
            const categorie = document.getElementById('artCategory').value;
            const contenu = tinymce.get('artContent').getContent();

            if (!titre) {
                alert('Veuillez saisir un titre pour l\'article.');
                return;
            }
            if (!auteur) {
                alert('Veuillez sélectionner un auteur.');
                return;
            }
            if (!categorie) {
                alert('Veuillez sélectionner une catégorie.');
                return;
            }
            if (!contenu || contenu.trim() === '') {
                alert('Veuillez saisir le contenu de l\'article.');
                return;
            }

            // Récupérer les recommandations SEO
            updateSeoScoreFromServer();
            openSeoModal();
        }

        function openSeoModal() {
            document.getElementById('seoModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSeoModal() {
            document.getElementById('seoModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function submitForm() {
            tinymce.triggerSave();
            const form = document.getElementById('articleForm');

            // Ajouter les données de la galerie au formulaire
            galleryImagesData.forEach((img, idx) => {
                if (img.file) {
                    // Nouveau fichier
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.name = 'gallery_images[]';
                    input.style.display = 'none';

                }

                // Ajouter les champs ALT et paragraphe
                const altInput = document.createElement('input');
                altInput.type = 'hidden';
                altInput.name = `gallery_alt[]`;
                altInput.value = img.alt;
                form.appendChild(altInput);

                const paraInput = document.createElement('input');
                paraInput.type = 'hidden';
                paraInput.name = `gallery_paragraphe[]`;
                paraInput.value = img.paragraphe;
                form.appendChild(paraInput);
            });

            // Pour les fichiers, on doit utiliser FormData
            const formData = new FormData(form);

            // Ajouter les fichiers de galerie
            galleryImagesData.forEach((img, idx) => {
                if (img.file) {
                    formData.append('gallery_images[]', img.file);
                } else if (img.isExisting && img.id) {
                    formData.append('existing_gallery_ids[]', img.id);
                    formData.append(`existing_gallery_alt_${idx}`, img.alt);
                    formData.append(`existing_gallery_paragraphe_${idx}`, img.paragraphe);
                }
            });

            // Soumettre via fetch
            fetch('edition.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    window.location.href = window.location.href;
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la soumission');
                    window.location.href = window.location.href;
                });

            closeSeoModal();
        }

        window.previewCover = previewCover;
        window.prepareGalleryImages = prepareGalleryImages;
        window.removeGalleryItem = removeGalleryItem;
        window.updateAlt = updateAlt;
        window.updateParagraphe = updateParagraphe;
        window.validateAndShowSEO = validateAndShowSEO;
        window.openSeoModal = openSeoModal;
        window.closeSeoModal = closeSeoModal;
        window.submitForm = submitForm;
    </script>
</body>

</html>