<?php

namespace App\Repository;

use App\Entity\Business;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Business|null find($id, $lockMode = null, $lockVersion = null)
 * @method Business|null findOneBy(array $criteria, array $orderBy = null)
 * @method Business[]    findAll()
 * @method Business[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BusinessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Business::class);
    }

    public function findBySearch($value)
    {
        $qb = $this->createQueryBuilder('b');
        $qb->select('b.id, b.title, b.address, b.city, b.state, b.phone, b.description')
            ->addSelect('c.name AS category')
            ->innerJoin('b.category', 'c')
            ->where($qb->expr()->like('b.title', ':search'))
            ->orWhere($qb->expr()->like('b.address', ':search'))
            ->orWhere($qb->expr()->like('b.zipcode', ':search'))
            ->orWhere($qb->expr()->like('b.city', ':search'))
            ->orWhere($qb->expr()->like('c.name', ':search'))
            ->orderBy('b.title', 'ASC')
            -> setParameter('search', '%'.$value.'%');
        return $qb->getQuery()->getResult();
    }
}
