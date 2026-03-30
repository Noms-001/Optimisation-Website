<?php

require_once __DIR__ . '/../repositories/UtilisateurRepository.php';

class UserService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function authentification(string $email, string $motDePasse): ?Utilisateur
    {
        $user = $this->repository->findByEmail($email);

        if (!$user) {
            return null;
        }

        // Vérification mot de passe hashé
        if (!password_verify($motDePasse, $user->mot_de_passe)) {
            return null;
        }

        return $user;
    }
}