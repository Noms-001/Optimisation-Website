<?php

require_once __DIR__ . '/../models/TypeArticle.php';

class TypeArticleRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT id_type AS id, libelle FROM type_article";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $types = [];

        foreach ($rows as $row) {
            $types[] = new TypeArticle($row);
        }

        return $types;
    }
}