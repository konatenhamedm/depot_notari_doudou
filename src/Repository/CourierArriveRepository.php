<?php

namespace App\Repository;

use App\Entity\CourierArrive;
use App\Entity\Fichier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CourierArrive|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourierArrive|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourierArrive[]    findAll()
 * @method CourierArrive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourierArriveRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourierArrive::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CourierArrive $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getNombre()
    {
        return $this->createQueryBuilder("a")
            ->select("count(a.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getFichier($value)
    {
        return $this->createQueryBuilder("a")
            ->select("f.path", "f.alt")
            /* ->innerJoin('a.fichier', 'f') */
            ->where('a.id=:id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(CourierArrive $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function countAll($searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(id),t.numero
FROM courier_arrive as t
WHERE t.type = "ARRIVE" and  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero']);



        $stmt = $connection->executeQuery($sql, $params);


        return intval($stmt->fetchOne());
    }


    public function countAllDepart($searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(id),t.numero
FROM courier_arrive as t
WHERE t.type = "DEPART" and  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero']);



        $stmt = $connection->executeQuery($sql, $params);


        return intval($stmt->fetchOne());
    }


    public function countAllInterne($searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(id),t.numero
FROM courier_arrive as t
WHERE t.type = "INTERNE" and  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero']);



        $stmt = $connection->executeQuery($sql, $params);


        return intval($stmt->fetchOne());
    }



    public function getAll($limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT
t.id,
t.numero as numero,t.date_reception ,t.objet,t.expediteur
FROM courier_arrive t
WHERE t.type = "ARRIVE" and  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero', 'expediteur']);

        $sql .= ' ORDER BY numero';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }



        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }



    public function getAllInterne($limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT
t.id,
t.numero as numero,t.date_reception ,t.objet,t.expediteur
FROM courier_arrive t
WHERE t.type = "INTERNE" and  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero', 'expediteur']);

        $sql .= ' ORDER BY numero';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }



        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }

    public function getAllDepart($limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT
t.id,
t.numero as numero,t.date_reception ,t.objet,t.expediteur
FROM courier_arrive t
WHERE t.type = "DEPART" and  1 = 1
SQL;
        $params = [];

        $sql .= $this->getSearchColumns($searchValue, $params, ['numero', 'expediteur']);

        $sql .= ' ORDER BY numero';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }



        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }
    // /**
    //  * @return CourierArrive[] Returns an array of CourierArrive objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CourierArrive
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
