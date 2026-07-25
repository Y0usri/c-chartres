<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Player;
use App\Entity\Review;
use App\Entity\Category;
use App\Entity\Level;

class AdminDashboardService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getSummary(): array
    {
        $conn = $this->em->getConnection();
        $counts = [];
        foreach ([
            'players' => 'SELECT COUNT(*) c FROM player',
            'reviews' => 'SELECT COUNT(*) c FROM review',
            'categories' => 'SELECT COUNT(*) c FROM category',
            'levels' => 'SELECT COUNT(*) c FROM level',
            'users' => 'SELECT COUNT(*) c FROM app_user'
        ] as $key => $sql) {
            $counts[$key] = (int)$conn->executeQuery($sql)->fetchOne();
        }
        $avgGlobal = (float)$conn->executeQuery('SELECT COALESCE(AVG(rating),0) FROM review')->fetchOne();
        return [
            'counts' => $counts,
            'avgGlobal' => $avgGlobal,
        ];
    }

    /**
     * Retourne les meilleurs joueurs avec moyenne et nombre d'avis.
     * @return array<int,array{player:Player,avg:float,count:int}>
     */
    public function getTopPlayers(int $limit = 5): array
    {
        // Les alias "HIDDEN" servent uniquement au ORDER BY : Doctrine ne les
        // hydrate jamais dans le resultat (chaque ligne reste une entite Player),
        // d'ou le recalcul explicite de la moyenne et du nombre d'avis ci-dessous.
        $qb = $this->em->getRepository(Player::class)->createQueryBuilder('p')
            ->leftJoin('p.reviews', 'r')
            ->addSelect('COALESCE(AVG(r.rating),0) AS HIDDEN avgRating')
            ->addSelect('COUNT(r.id) AS HIDDEN reviewCount')
            ->groupBy('p.id')
            ->orderBy('avgRating', 'DESC')
            ->addOrderBy('reviewCount', 'DESC')
            ->setMaxResults($limit);
        $players = $qb->getQuery()->getResult();

        return array_map(fn(Player $player) => [
            'player' => $player,
            'avg' => $this->getAverageForPlayer($player),
            'count' => $this->getReviewCountForPlayer($player),
        ], $players);
    }

    private function getAverageForPlayer(Player $player): float
    {
        $val = $this->em->createQueryBuilder()
            ->select('COALESCE(AVG(r.rating),0)')
            ->from(Review::class, 'r')
            ->where('r.player = :p')
            ->setParameter('p', $player)
            ->getQuery()->getSingleScalarResult();
        return (float)$val;
    }

    private function getReviewCountForPlayer(Player $player): int
    {
        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(r.id)')
            ->from(Review::class, 'r')
            ->where('r.player = :p')
            ->setParameter('p', $player)
            ->getQuery()->getSingleScalarResult();
    }
}
