<?php

require_once __DIR__ . '/../repositories/TypeArticleRepository.php';

class TypeArticleService
{
    private TypeArticleRepository $repository;

    public function __construct(TypeArticleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}