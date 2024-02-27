<?php

namespace App\Repository;

use App\Entity\DocumentCourier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentCourier>
 *
 * @method DocumentCourier|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentCourier|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentCourier[]    findAll()
 * @method DocumentCourier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentCourierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentCourier::class);
    }

    public function add(DocumentCourier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getFichier($value)
    {
        return $this->createQueryBuilder("d")
            ->select("f.path", "f.alt")
            ->innerJoin('d.fichier', 'f')
            ->innerJoin('d.courier', 'c')
            ->where('c.id=:id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getResult();
    }

    public function remove(DocumentCourier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return DocumentCourier[] Returns an array of DocumentCourier objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DocumentCourier
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
