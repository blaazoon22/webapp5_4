<?php

namespace App\Repository;

use App\Entity\PostCodeData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostCodeData|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostCodeData|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostCodeData[]    findAll()
 * @method PostCodeData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCodeDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostCodeData::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(PostCodeData $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(PostCodeData $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    /**
     * @return PostCodeData[] 
     */
    public function findPostCodeData(string $searchX): array
    {
        return $this->createQueryBuilder('p')

            ->select('JSON_EXTRACT(p.postcodejson, :jsonPath)')
            ->andWhere("JSON_EXTRACT(p.postcodejson, :jsonPath) LIKE :val")  
            ->setParameter('jsonPath', '$.postcode')
            ->setParameter('val', '%'.$searchX.'%')
            ->getQuery()
            ->getResult()             
        ;
    }


    /**
     * @return PostCodeData[] 
     */
    public function findLatLongPostCode(float $lat, float $long): array
    {
        return $this->createQueryBuilder('p')

            ->select('JSON_EXTRACT(p.postcodejson, :jsonPathA)')
            ->andWhere("JSON_EXTRACT(p.postcodejson, :jsonPathB) <= :latV OR JSON_EXTRACT(p.postcodejson, :jsonPathC) >= :longV") 
            ->setParameter('jsonPathA', '$.postcode')
            ->setParameter('jsonPathB', '$.wgs84_lat')
            ->setParameter('jsonPathC', '$.wgs84_lon')
            ->setParameter('latV', '%'.$lat.'%')
            ->setParameter('longV', '%'.$long.'%')
            ->getQuery()
            ->getResult()             
        ;
    }




    

    // /**
    //  * @return PostCodeData[] Returns an array of PostCodeData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PostCodeData
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
