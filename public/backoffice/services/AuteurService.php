<?php

require_once __DIR__ . '/../repositories/AuteurRepository.php';

class AuteurService
{
    private AuteurRepository $repository;

    public function __construct(AuteurRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}