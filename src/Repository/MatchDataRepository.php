<?php

namespace App\Repository;

use App\Entity\MatchData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MatchData|null find($id, $lockMode = null, $lockVersion = null)
 * @method MatchData|null findOneBy(array $criteria, array $orderBy = null)
 * @method MatchData[]    findAll()
 * @method MatchData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchData::class);
    }

    // /**
    //  * @return MatchData[] Returns an array of MatchData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MatchData
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
