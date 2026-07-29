<?php

namespace App\Repository;

use App\Entity\Vehicule;
use App\Enum\CategorieVehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicule>
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    /**
     * @param CategorieVehicule[] $categories
     *
     * @return Vehicule[]
     */
    public function rechercher(?string $recherche, array $categories, ?float $prixMax): array
    {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.disponible = true')
            ->orderBy('v.prixJour', 'ASC');

        if ($recherche) {
            $qb->andWhere('v.marque LIKE :recherche OR v.modele LIKE :recherche')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        if ([] !== $categories) {
            $qb->andWhere('v.categorie IN (:categories)')
                ->setParameter('categories', $categories);
        }

        if (null !== $prixMax) {
            $qb->andWhere('v.prixJour <= :prixMax')
                ->setParameter('prixMax', $prixMax);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Vehicule[] Returns an array of Vehicule objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vehicule
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
