<?php

require_once __DIR__ . '/../../commons/models/Utilisateur.php';

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?Utilisateur
    {
        $sql = "SELECT id_utilisateur AS id, email, mot_de_passe FROM utilisateur WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new Utilisateur($data);
    }
}