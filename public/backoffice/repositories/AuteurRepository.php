<?php

require_once __DIR__ . '/../../commons/models/Auteur.php';

class AuteurRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT id_auteur AS id, nom, email, role FROM auteur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $auteurs = [];

        foreach ($rows as $row) {
            $auteurs[] = new Auteur($row);
        }

        return $auteurs;
    }
}