<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * Retourne un tableau [ playerId => moyenne ] pour un ensemble d'ids.
     * @param int[] $ids
     * @return array<int,float>
     */
    public function getAverageRatingsForPlayers(array $ids): array
    {
        if (count($ids) === 0) { return []; }
        $qb = $this->createQueryBuilder('p')
            ->select('p.id AS pid, COALESCE(AVG(r.rating),0) AS avgRating')
            ->leftJoin('p.reviews', 'r')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->groupBy('p.id');
        $rows = $qb->getQuery()->getArrayResult();
        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['pid']] = (float)$row['avgRating'];
        }
        return $map;
    }

    /**
     * Retourne la moyenne d'un joueur.
     */
    public function getAverageRatingForPlayer(int $id): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('COALESCE(AVG(r.rating),0) AS avgRating')
            ->leftJoin('p.reviews','r')
            ->where('p.id = :id')
            ->setParameter('id',$id)
            ->getQuery()->getSingleScalarResult();
        return (float)$result;
    }

    /**
     * Recherche paginée avec filtres.
     * @return array{data: Player[], total: int}
     */
    public function searchPaginated(array $criteria, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category','c')->addSelect('c')
            ->leftJoin('p.level','l')->addSelect('l');

        if (!empty($criteria['q'])) {
            $qb->andWhere('LOWER(p.firstName) LIKE :q OR LOWER(p.lastName) LIKE :q')
               ->setParameter('q','%'.mb_strtolower($criteria['q']).'%');
        }
        if (!empty($criteria['category'])) {
            $qb->andWhere('c.id = :cid')->setParameter('cid', $criteria['category']);
        }
        if (!empty($criteria['level'])) {
            $qb->andWhere('l.id = :lid')->setParameter('lid', $criteria['level']);
        }

        // On calcule la moyenne à la volée si minAvg demandé
        $minAvg = $criteria['minAvg'] ?? null;
        if ($minAvg !== null && $minAvg !== '') {
            $qb->leftJoin('p.reviews','r_avg');
            // On garde un select caché si besoin d'ordonner plus tard
            $qb->addSelect('COALESCE(AVG(r_avg.rating),0) AS HIDDEN avgFilter');
            $qb->groupBy('p.id');
            // Utiliser directement l'expression dans HAVING (l'alias HIDDEN n'est pas disponible ici)
            $qb->having('COALESCE(AVG(r_avg.rating),0) >= :minAvg')->setParameter('minAvg', (float)$minAvg);
        }

        // Clone pour total (sans pagination)
        $countQb = clone $qb;
        if ($minAvg !== null && $minAvg !== '') {
            $countQb->select('COUNT(DISTINCT p.id)');
            $total = (int)$countQb->getQuery()->getSingleScalarResult();
        } else {
            $countQb->select('COUNT(p.id)');
            $total = (int)$countQb->getQuery()->getSingleScalarResult();
        }

        $qb->setFirstResult(($page-1)*$limit)->setMaxResults($limit);
        $data = $qb->getQuery()->getResult();
        return ['data'=>$data, 'total'=>$total];
    }
}
