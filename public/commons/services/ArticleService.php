<?php

require_once __DIR__ . '/../repositories/ArticleRepository.php';

class ArticleService
{
    private ArticleRepository $repository;

    public function __construct(ArticleRepository $repository)
    {
        $this->repository = $repository;
    }

    # =========================
    # SAVE (logique métier)
    # =========================
    public function save(Article $article): Article
    {
        // 1. Validation basique
        $this->validate($article);

        // 2. Normalisation des données
        $this->normalize($article);

        // 3. Sauvegarde DB
        return $this->repository->save($article);
    }

    # =========================
    # DELETE
    # =========================
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    # =========================
    # VALIDATION
    # =========================
    private function validate(Article $article): void
    {
        if (empty($article->titre)) {
            throw new Exception("Le titre est obligatoire");
        }

        if (strlen($article->titre) < 10) {
            throw new Exception("Titre trop court");
        }

        if (empty($article->metaDescription)) {
            throw new Exception("Meta description obligatoire");
        }

        if (empty($article->contenu)) {
            throw new Exception("Contenu obligatoire");
        }

        if (empty($article->auteur)) {
            throw new Exception("Auteur obligatoire");
        }

        if (empty($article->type)) {
            throw new Exception("Type d'article obligatoire");
        }
    }

    # =========================
    # NORMALISATION
    # =========================
    private function normalize(Article $article): void
    {
        $article->titre = trim($article->titre);
        $article->metaDescription = trim($article->metaDescription);
        $article->contenu = trim($article->contenu);

        if ($article->priorite === null) {
            $article->priorite = 0;
        }

        if ($article->datePublication === null) {
            $article->datePublication = date("Y-m-d H:i:s");
        }
    }

    public function getById(int $id): ?Article
    {
        if ($id <= 0) {
            throw new Exception("ID invalide");
        }

        return $this->repository->findById($id);
    }

    public function getAll(?string $motCle = null, int $page = 1, int $limit = 10): array
    {
        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        return $this->repository->findAll($motCle, $limit, $offset);
    }

    public function getArticlesLiees(int $id): array
    {
        if ($id <= 0) {
            throw new Exception("ID invalide");
        }

        return $this->repository->findArticlesLiees($id);
    }

    public function getArticlesRecentes(int $limit = 10): array
    {
        if ($limit <= 0) {
            $limit = 10;
        }

        return $this->repository->findArticlesRecentes($limit);
    }

    public function getArticlesPlusLues(int $limit = 10): array
    {
        if ($limit <= 0) {
            $limit = 10;
        }

        return $this->repository->findArticlesPlusLues($limit);
    }

    public function getArticlesAnalysesRapports(int $limit = 10): array
    {
        if ($limit <= 0) {
            $limit = 10;
        }

        return $this->repository->findArticlesAnalysesRapports($limit);
    }
}
