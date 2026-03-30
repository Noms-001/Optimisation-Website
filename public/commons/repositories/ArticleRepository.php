<?php

require_once __DIR__ . '/../models/Article.php';

class ArticleRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    # =========================
    # SAVE (INSERT ou UPDATE)
    # =========================
    public function save(Article $article): Article
    {
        if ($article->id) {
            return $this->update($article);
        }

        return $this->insert($article);
    }

    # =========================
    # INSERT
    # =========================
    private function insert(Article $article): Article
    {
        $sql = "INSERT INTO article 
        (titre, meta_description, contenu, mot_cle_principal, mot_cle_secondaire, priorite, nombre_vue, image_src, image_alt, date_publication, id_auteur, id_type)
        VALUES
        (:titre, :meta_description, :contenu, :mot_cle_principal, :mot_cle_secondaire, :priorite, 0, :image_src, :image_alt, :date_publication, :id_auteur, :id_type)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            "titre" => $article->titre,
            "meta_description" => $article->metaDescription,
            "contenu" => $article->contenu,
            "mot_cle_principal" => $article->motClePrincipal,
            "mot_cle_secondaire" => $article->motCleSecondaire,
            "priorite" => $article->priorite,
            "image_src" => $article->imageSrc,
            "image_alt" => $article->imageAlt,
            "date_publication" => $article->datePublication,
            "id_auteur" => $article->auteur,
            "id_type" => $article->type
        ]);

        $article->id = $this->pdo->lastInsertId();

        // sauvegarde media si existant
        $this->saveMedia($article);

        return $article;
    }

    # =========================
    # UPDATE
    # =========================
    private function update(Article $article): Article
    {
        $sql = "UPDATE article SET
            titre = :titre,
            meta_description = :meta_description,
            contenu = :contenu,
            mot_cle_principal = :mot_cle_principal,
            mot_cle_secondaire = :mot_cle_secondaire,
            priorite = :priorite,
            image_src = :image_src,
            image_alt = :image_alt,
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
            "image_src" => $article->imageSrc,
            "image_alt" => $article->imageAlt,
            "date_publication" => $article->datePublication,
            "id_auteur" => $article->auteur,
            "id_type" => $article->type,
            "id" => $article->id
        ]);

        // refresh media
        $this->deleteMedia($article->id);
        $this->saveMedia($article);

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

        $sql = "INSERT INTO media (src, alt, id_article) VALUES (:src, :id_article)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($article->media as $media) {

            $src = $media->src ?? null;
            $alt = $media->alt ?? "";

            if (empty($src)) continue;

            $stmt->execute([
                "src" => $src,
                "alt" => $alt,
                "id_article" => $article->id
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
                a.image_src AS imageSrc,
                a.image_alt AS imageAlt,
                a.date_publication AS datePublication,
                a.id_auteur AS auteur,
                a.id_type AS type
            FROM article a
            WHERE a.id_article = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $article = new Article($data);

        // Charger les médias
        $article->media = $this->findMediaByArticleId($id);

        return $article;
    }

    private function findMediaByArticleId(int $id): array
    {
        $sql = "SELECT src FROM media WHERE id_article = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function findAll(?string $motCle = null, int $limit = 10, int $offset = 0): array
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
            a.image_src AS imageSrc,
            a.image_alt AS imageAlt,
            a.date_publication AS datePublication,
            a.id_auteur AS auteur,
            a.id_type AS type,
            t.libelle AS typeLibelle
        FROM article a
        LEFT JOIN type_article t ON a.id_type = t.id_type
        WHERE 1=1
    ";

        $params = [];

        // 🔎 FILTRE MOT CLÉ (titre + mot_cle + catégorie)
        if (!empty($motCle)) {
            $sql .= "
            AND (
                a.titre LIKE :motCle
                OR a.mot_cle LIKE :motCle
                OR t.libelle LIKE :motCle
            )
        ";

            $params["motCle"] = "%" . $motCle . "%";
        }

        // 📄 pagination
        $sql .= " ORDER BY a.date_publication DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        // bind paramètres dynamiques
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

            // charger media pour chaque article
            $article->media = $this->findMediaByArticleId($article->id);

            $articles[] = $article;
        }

        return $articles;
    }

    public function findArticlesLiees(int $id): array
    {
        // On récupère d'abord l'article courant (auteur + catégorie)
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
            a.image_src AS imgSrc,
            a.image_alt AS imgAlt,
            a.date_publication AS datePublication,
            a.id_auteur AS auteur,
            a.id_type AS type
        FROM article a
        WHERE a.id_article != :id
          AND (
                a.id_auteur = :auteur
                OR a.id_type = :type
          )
        ORDER BY a.date_publication DESC
        LIMIT 5
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
            $art->media = $this->findMediaByArticleId($art->id);
            $articles[] = $art;
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
            a.image_src AS imgSrc,
            a.image_alt AS imgAlt,
            a.date_publication AS datePublication,
            a.id_auteur AS auteur,
            a.id_type AS type
        FROM article a
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
            $article->media = $this->findMediaByArticleId($article->id);
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
            a.image_src AS imgSrc,
            a.image_alt AS imgAlt,
            a.date_publication AS datePublication,
            a.id_auteur AS auteur,
            a.id_type AS type
        FROM article a
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
            $article->media = $this->findMediaByArticleId($article->id);
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
            a.image_src AS imageSrc,
            a.image_alt AS imageAlt,
            a.date_publication AS datePublication,
            a.id_auteur AS auteur,
            a.id_type AS type,
            t.libelle AS typeLibelle
        FROM article a
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
            $article->media = $this->findMediaByArticleId($article->id);
            $articles[] = $article;
        }

        return $articles;
    }
}
