<?php

namespace App\Repository;

use App\Entity\Dossier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dossier>
 *
 * @method Dossier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dossier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dossier[]    findAll()
 * @method Dossier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DossierRepository extends ServiceEntityRepository
{
    use TableInfoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dossier::class);
    }

    public function add(Dossier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Dossier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getListe($etat, $titre)
    {
        return $this->createQueryBuilder("d")
            ->innerJoin('d.typeActe', 't')
            ->where('d.active=:active')
            ->andWhere('d.etat=:etat')
            ->andWhere('t.titre=:titre')
            ->setParameters(array('active' => 1, 'etat' => $etat, 'titre' => $titre))
            ->getQuery()
            ->getResult();
    }

    public function listeActe($acte)
    {
        return $this->createQueryBuilder("d")
            ->innerJoin('d.typeActe', 't')
            ->where('d.active=:active')
            ->andWhere('t.id=:acte')
            ->setParameters(array('active' => 1, 'acte' => $acte))
            ->getQuery()
            ->getResult();
    }


    public function countAll($etat, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $sql = <<<SQL
SELECT COUNT(id)
FROM dossier
WHERE  1 = 1
SQL;
        $params = [];

        if ($etat == 'termine') {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.termine') = 1)";
        } elseif ($etat == 'archive') {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.archive') = 1)";
        } else {
            $sql .= " AND ((JSON_CONTAINS(etat, '1', '$.cree') = 1) or (JSON_CONTAINS(etat, '1', '$.en_cours')= 1))";
        }



        $sql .= $this->getSearchColumns($searchValue, $params, ['d.numero_ouverture']);



        $stmt = $connection->executeQuery($sql, $params);


        return intval($stmt->fetchOne());
    }



    public function getAll($etat, $limit, $offset, $searchValue = null)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $sql = <<<SQL
SELECT
id,
date_creation,
numero_ouverture,
objet,
etape,
type_acte_id
FROM dossier
WHERE  1 = 1
SQL;
        $params = [];


        if ($etat == 'termine') {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.termine') = 1)";
        } elseif ($etat == 'archive') {
            $sql .= " AND (JSON_CONTAINS(etat, '1', '$.archive') = 1)";
        } else {
            $sql .= " AND ((JSON_CONTAINS(etat, '1', '$.cree') = 1) or (JSON_CONTAINS(etat, '1', '$.en_cours') = 1))";
        }

        $sql .= $this->getSearchColumns($searchValue, $params, ['d.numero_ouverture']);

        $sql .= ' ORDER BY date_creation DESC';

        if ($limit && $offset == null) {
            $sql .= " LIMIT {$limit}";
        } else if ($limit && $offset) {
            $sql .= " LIMIT {$offset},{$limit}";
        }



        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }



    //    /**
    //     * @return Dossier[] Returns an array of Dossier objects
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

    //    public function findOneBySomeField($value): ?Dossier
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
