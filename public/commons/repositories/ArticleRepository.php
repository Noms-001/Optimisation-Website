<?php

require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Auteur.php';
require_once __DIR__ . '/../models/Media.php';
require_once __DIR__ . '/../services/ImageService.php';

class ArticleRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Article $article, ?array $coverFile = null, ?array $mediaFiles = null): Article
    {
        // =========================
        // COVER UPLOAD
        // =========================
        if ($coverFile && !empty($coverFile['tmp_name'])) {
            $article->imageSrc = ImageService::uploadAndGenerateImages(
                $coverFile,
                __DIR__ . '/../../uploads/',
                [
                    'cover' => [800, 450],
                    'thumb' => [200, 130],
                    'mini'  => [70, 45]
                ]
            );
        }

        // INSERT OR UPDATE
        if ($article->id) {
            $article = $this->update($article);
        } else {
            $article = $this->insert($article);
        }

        // =========================
        // MEDIA UPLOAD (après insert/update)
        // =========================
        if (!empty($mediaFiles)) {

            $article->media = [];

            foreach ($mediaFiles as $fileData) {
                error_log("Mon message debug: " . $fileData['paragraphe'] . ".");


                if (empty($fileData['file']['tmp_name'])) continue;

                $src = ImageService::uploadAndGenerateImages(
                    $fileData['file'],
                    __DIR__ . '/../../uploads/',
                    [
                        'thumb' => [200, 130]
                    ]
                );

                $media = new Media([
                    "src" => $src,
                    "alt" => $fileData['alt'] ?? "",
                    "article" => $article->id,
                    "paragraphe" => $fileData['paragraphe'] ?? 1
                ]);

                $article->media[] = $media;
            }

            $this->deleteMedia($article->id);
            $this->saveMedia($article);
        }

        return $article;
    }

    public function updateMedia(Article $article): void
    {
        // Supprimer les médias existants qui ne sont plus dans la liste
        if (!empty($article->media)) {
            $this->deleteMedia($article->id);
            $this->saveMedia($article);
        }
    }

    private function update(Article $article): Article
    {
        $sql = "UPDATE article SET
                    titre = :titre,
                    meta_description = :meta_description,
                    contenu = :contenu,
                    mot_cle_principal = :mot_cle_principal,
                    mot_cle_secondaire = :mot_cle_secondaire,
                    priorite = :priorite,
                    img_src = :img_src,
                    img_alt = :img_alt,
                    date_publication = :date_publication,
                    id_auteur = :id_auteur,
                    id_type = :id_type
                WHERE id_article = :id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            "titre" => $article->titre,
            "meta_description" => $article->metaDescription,
            "contenu" => $article->contenu,
            "mot_cle_principal" => $article->motClePrincipal,
            "mot_cle_secondaire" => $article->motCleSecondaire,
            "priorite" => $article->priorite,
            "img_src" => $article->imageSrc,
            "img_alt" => $article->imageAlt,
            "date_publication" => $article->datePublication,
            "id_auteur" => $article->auteur,
            "id_type" => $article->type,
            "id" => $article->id
        ]);

        return $article;
    }

    private function insert(Article $article): Article
    {
        $sql = "INSERT INTO article 
                (titre, meta_description, contenu, mot_cle_principal, mot_cle_secondaire, priorite, nombre_vue, img_src, img_alt, date_publication, id_auteur, id_type)
                VALUES
                (:titre, :meta_description, :contenu, :mot_cle_principal, :mot_cle_secondaire, :priorite, 0, :img_src, :img_alt, :date_publication, :id_auteur, :id_type)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            "titre" => $article->titre,
            "meta_description" => $article->metaDescription,
            "contenu" => $article->contenu,
            "mot_cle_principal" => $article->motClePrincipal,
            "mot_cle_secondaire" => $article->motCleSecondaire,
            "priorite" => $article->priorite,
            "img_src" => $article->imageSrc,
            "img_alt" => $article->imageAlt,
            "date_publication" => $article->datePublication,
            "id_auteur" => $article->auteur,
            "id_type" => $article->type
        ]);

        $article->id = $this->pdo->lastInsertId();

        return $article;
    }

    # =========================
    # DELETE ARTICLE
    # =========================
    public function delete(int $id): bool
    {
        $this->deleteMedia($id);

        $stmt = $this->pdo->prepare("DELETE FROM article WHERE id_article = :id");

        return $stmt->execute([
            "id" => $id
        ]);
    }

    # =========================
    # MEDIA HANDLING
    # =========================
    private function saveMedia(Article $article): void
    {
        if (empty($article->media)) return;

        $sql = "INSERT INTO media (src, alt, id_article, paragraphe) VALUES (:src, :alt, :id_article, :paragraphe)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($article->media as $media) {

            $src = $media->src ?? null;
            $alt = $media->alt ?? "";
            $paragraphe = $media->paragraphe ?? 1;

            if (empty($src)) continue;

            $stmt->execute([
                "src" => $src,
                "alt" => $alt,
                "id_article" => $article->id,
                "paragraphe" => $paragraphe
            ]);
        }
    }

    private function deleteMedia(int $articleId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM media WHERE id_article = :id");
        $stmt->execute(["id" => $articleId]);
    }

    public function findById(int $id): ?Article
    {
        $sql = "SELECT 
                    a.id_article AS id,
                    a.titre,
                    a.meta_description AS metaDescription,
                    a.contenu,
                    a.mot_cle_principal AS motClePrincipal,
                    a.mot_cle_secondaire AS motCleSecondaire,
                    a.priorite,
                    a.nombre_vue AS nombreVue,
                    a.img_src AS imageSrc,
                    a.img_alt AS imageAlt,
                    a.date_publication AS datePublication,
                    au.id_auteur AS auteur_id,
                    au.nom AS auteur_nom,
                    au.email AS auteur_email,
                    au.role AS auteur_role,
                    t.id_type AS type_id,
                    t.libelle AS type_libelle
                FROM article a
                LEFT JOIN auteur au ON a.id_auteur = au.id_auteur
                INNER JOIN type_article t ON a.id_type = t.id_type
                WHERE a.id_article = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $auteur = null;
        if ($data['auteur_id']) {
            $auteur = new Auteur([
                "id" => $data['auteur_id'],
                "nom" => $data['auteur_nom'],
                "email" => $data['auteur_email'],
                "role" => $data['auteur_role']
            ]);
        }

        $type = null;
        if ($data['type_id']) {
            $type = new TypeArticle([
                "id" => $data['type_id'],
                "libelle" => $data['type_libelle']
            ]);
        }

        $article = new Article($data);
        $article->auteur = $auteur;
        $article->type = $type;

        $article->media = $this->findMediaByArticleId($id);

        return $article;
    }

    private function findMediaByArticleId(int $id): array
    {
        $sql = "SELECT * FROM media WHERE id_article = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $media = [];

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $media[] = new Media([
                "id" => $row['id_media'],
                "src" => $row['src'],
                "alt" => $row['alt'],
                "paragraphe" => $row['paragraphe']
            ]);
        }

        return $media;
    }

    public function findAll(
        ?string $motCle = null,
        ?string $categorie = null,
        int $limit = 10,
        int $offset = 0
    ): array {
        $sql = "
            SELECT 
                a.id_article AS id,
                a.titre,
                a.meta_description AS metaDescription,
                a.contenu,
                a.mot_cle_principal AS motClePrincipal,
                a.mot_cle_secondaire AS motCleSecondaire,
                a.priorite,
                a.nombre_vue AS nombreVue,
                a.img_src AS imageSrc,
                a.img_alt AS imageAlt,
                a.date_publication AS datePublication,
                a.id_type AS type_id,
                au.id_auteur AS auteur_id,
                au.nom AS auteur_nom,
                au.email AS auteur_email,
                au.role AS auteur_role,
                t.id_type AS cat_id,
                t.libelle AS cat_libelle
            FROM article a
            LEFT JOIN auteur au ON a.id_auteur = au.id_auteur
            LEFT JOIN type_article t ON a.id_type = t.id_type
            WHERE 1=1
        ";

        $params = [];

        if (!empty($motCle)) {
            $sql .= "
            AND (
                a.titre LIKE :motCle
                OR a.mot_cle_principal LIKE :motCle
                OR a.mot_cle_secondaire LIKE :motCle
                OR t.libelle LIKE :motCle
            )
        ";

            $params["motCle"] = "%" . $motCle . "%";
        }

        if (!empty($categorie)) {
            $sql .= " AND t.libelle = :categorie ";
            $params["categorie"] = $categorie;
        }

        $sql .= " ORDER BY a.date_publication DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $articles = [];

        foreach ($rows as $row) {

            $article = new Article($row);

            $article->auteur = new Auteur([
                "id" => $row["auteur_id"],
                "nom" => $row["auteur_nom"],
                "email" => $row["auteur_email"],
                "role" => $row["auteur_role"]
            ]);

            $article->type = new TypeArticle([
                "id" => $row["cat_id"],
                "libelle" => $row["cat_libelle"]
            ]);

            $articles[] = $article;
        }

        return $articles;
    }

    public function countAll(?string $motCle = null, ?string $categorie = null): int
    {
        $sql = "SELECT COUNT(*) as total 
                    FROM article a
                    LEFT JOIN type_article t 
                        ON a.id_type = t.id_type 
                    WHERE 1=1";

        $params = [];

        if ($motCle) {
            $sql .= " AND (a.titre LIKE :motcle OR a.contenu LIKE :motcle)";
            $params[':motcle'] = "%$motCle%";
        }

        if ($categorie) {
            $sql .= " AND t.libelle = :categorie";
            $params[':categorie'] = $categorie;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetch()['total'];
    }

    public function findArticlesLiees(int $id): array
    {
        $sqlArticle = "SELECT id_auteur, id_type 
                   FROM article 
                   WHERE id_article = :id";

        $stmt = $this->pdo->prepare($sqlArticle);
        $stmt->execute(["id" => $id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            return [];
        }

        $sql = "
        SELECT 
            a.id_article AS id,
            a.titre,
            a.meta_description AS metaDescription,
            a.contenu,
            a.mot_cle_principal AS motClePrincipal,
            a.mot_cle_secondaire AS motCleSecondaire,
            a.priorite,
            a.nombre_vue AS nombreVue,
            a.img_src AS imageSrc,
            a.img_alt AS imageAlt,
            a.date_publication AS datePublication,
            au.id_auteur AS auteur_id,
            au.nom AS auteur_nom,
            au.email AS auteur_email,
            au.role AS auteur_role,
            t.id_type AS type_id,
            t.libelle AS type_libelle
        FROM article a
        LEFT JOIN auteur au ON a.id_auteur = au.id_auteur
        INNER JOIN type_article t ON a.id_type = t.id_type
        WHERE a.id_article != :id
          AND (
                a.id_auteur = :auteur
                OR a.id_type = :type
          )
        ORDER BY a.date_publication DESC
        LIMIT 2
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "id" => $id,
            "auteur" => $article["id_auteur"],
            "type" => $article["id_type"]
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $articles = [];

        foreach ($rows as $row) {
            $art = new Article($row);
            $art->auteur = new Auteur([
                "id" => $row["auteur_id"],
                "nom" => $row["auteur_nom"],
                "email" => $row["auteur_email"],
                "role" => $row["auteur_role"]
            ]);
            $art->type = new TypeArticle([
                "id" => $row["type_id"],
                "libelle" => $row["type_libelle"]
            ]);
            $articles[] = $art;
        }

        return $articles;
    }

    public function getAlaUne(int $limit = 5): array
    {
        $sql = "
            SELECT 
                a.id_article AS id,
                a.titre,
                a.meta_description AS metaDescription,
                a.contenu,
                a.mot_cle_principal AS motClePrincipal,
                a.mot_cle_secondaire AS motCleSecondaire,
                a.priorite,
                a.nombre_vue AS nombreVue,
                a.img_src AS imageSrc,
                a.img_alt AS imageAlt,
                a.date_publication AS datePublication,
                au.id_auteur AS auteur_id,
                au.nom AS auteur_nom,
                au.email AS auteur_email,
                au.role AS auteur_role,
                t.id_type AS type_id,
                t.libelle AS type_libelle
            FROM article a
            LEFT JOIN auteur au ON a.id_auteur = au.id_auteur
            INNER JOIN type_article t ON a.id_type = t.id_type
            WHERE a.priorite = 1
            ORDER BY a.date_publication DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $articles = [];

        foreach ($rows as $row) {
            $article = new Article($row);
            $article->auteur = new Auteur([
                "id" => $row["auteur_id"],
                "nom" => $row["auteur_nom"],
                "email" => $row["auteur_email"],
                "role" => $row["auteur_role"]
            ]);
            $article->type = new TypeArticle([
                "id" => $row["type_id"],
                "libelle" => $row["type_libelle"]
            ]);

            $articles[] = $article;
        }

        return $articles;
    }

    public function findArticlesRecentes(int $limit = 10): array
    {
        $sql = "
            SELECT 
                a.id_article AS id,
                a.titre,
                a.meta_description AS metaDescription,
                a.contenu,
                a.mot_cle_principal AS motClePrincipal,
                a.mot_cle_secondaire AS motCleSecondaire,
                a.priorite,
                a.nombre_vue AS nombreVue,
                a.img_src AS imageSrc,
                a.img_alt AS imageAlt,
                a.date_publication AS datePublication,
                au.id_auteur AS auteur_id,
                au.nom AS auteur_nom,
                au.email AS auteur_email,
                au.role AS auteur_role,
                t.id_type AS type_id,
                t.libelle AS type_libelle
            FROM article a
            LEFT JOIN auteur au ON a.id_auteur = au.id_auteur
            INNER JOIN type_article t ON a.id_type = t.id_type
            WHERE a.date_publication >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY a.date_publication DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $articles = [];

        foreach ($rows as $row) {
            $article = new Article($row);
            $article->auteur = new Auteur([
                "id" => $row["auteur_id"],
                "nom" => $row["auteur_nom"],
                "email" => $row["auteur_email"],
                "role" => $row["auteur_role"]
            ]);
            $article->type = new TypeArticle([
                "id" => $row["type_id"],
                "libelle" => $row["type_libelle"]
            ]);
            $articles[] = $article;
        }

        return $articles;
    }

    public function findArticlesPlusLues(int $limit = 10): array
    {
        $sql = "
            SELECT 
                a.id_article AS id,
                a.titre,
                a.meta_description AS metaDescription,
                a.contenu,
                a.mot_cle_principal AS motClePrincipal,
                a.mot_cle_secondaire AS motCleSecondaire,
                a.priorite,
                a.nombre_vue AS nombreVue,
                a.img_src AS imageSrc,
                a.img_alt AS imageAlt,
                a.date_publication AS datePublication,
                au.id_auteur AS auteur_id,
                au.nom AS auteur_nom,
                au.email AS auteur_email,
                au.role AS auteur_role,
                t.id_type AS type_id,
                t.libelle AS type_libelle
            FROM article a
            LEFT JOIN auteur au ON a.id_auteur = au.id_auteur
            INNER JOIN type_article t ON a.id_type = t.id_type
            ORDER BY a.nombre_vue DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $articles = [];

        foreach ($rows as $row) {
            $article = new Article($row);
            $article->auteur = new Auteur([
                "id" => $row["auteur_id"],
                "nom" => $row["auteur_nom"],
                "email" => $row["auteur_email"],
                "role" => $row["auteur_role"]
            ]);
            $article->type = new TypeArticle([
                "id" => $row["type_id"],
                "libelle" => $row["type_libelle"]
            ]);
            $articles[] = $article;
        }

        return $articles;
    }

    public function findArticlesAnalysesRapports(int $limit = 10): array
    {
        $sql = "
            SELECT 
                a.id_article AS id,
                a.titre,
                a.meta_description AS metaDescription,
                a.contenu,
                a.mot_cle_principal AS motClePrincipal,
                a.mot_cle_secondaire AS motCleSecondaire,
                a.priorite,
                a.nombre_vue AS nombreVue,
                a.img_src AS imageSrc,
                a.img_alt AS imageAlt,
                a.date_publication AS datePublication,
                au.id_auteur AS auteur_id,
                au.nom AS auteur_nom,
                au.email AS auteur_email,
                au.role AS auteur_role,
                t.id_type AS type_id,
                t.libelle AS type_libelle
            FROM article a
            LEFT JOIN auteur au ON a.id_auteur = au.id_auteur
            INNER JOIN type_article t ON a.id_type = t.id_type
            WHERE a.date_publication >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND LOWER(t.libelle) IN ('analyse', 'rapport')
            ORDER BY a.date_publication DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $articles = [];

        foreach ($rows as $row) {
            $article = new Article($row);
            $article->auteur = new Auteur([
                "id" => $row["auteur_id"],
                "nom" => $row["auteur_nom"],
                "email" => $row["auteur_email"],
                "role" => $row["auteur_role"]
            ]);
            $article->type = new TypeArticle([
                "id" => $row["type_id"],
                "libelle" => $row["type_libelle"]
            ]);
            $articles[] = $article;
        }

        return $articles;
    }
}
