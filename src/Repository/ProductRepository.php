<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Search products by name or description containing the given keyword.
     * @return Product[]
     */
    public function searchByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :kw OR p.description LIKE :kw')
            ->setParameter('kw', '%'.$keyword.'%')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}