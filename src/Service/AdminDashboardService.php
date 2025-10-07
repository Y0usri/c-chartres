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
        $qb = $this->em->getRepository(Player::class)->createQueryBuilder('p')
            ->leftJoin('p.reviews', 'r')
            ->addSelect('COALESCE(AVG(r.rating),0) AS HIDDEN avgRating')
            ->addSelect('COUNT(r.id) AS HIDDEN reviewCount')
            ->groupBy('p.id')
            ->orderBy('avgRating', 'DESC')
            ->addOrderBy('reviewCount', 'DESC')
            ->setMaxResults($limit);
        $rows = $qb->getQuery()->getResult();
        $result = [];
        foreach ($rows as $row) {
            if ($row instanceof Player) {
                $result[] = [
                    'player' => $row,
                    'avg' => 0.0,
                    'count' => 0,
                ];
            } else {
                // Quand Doctrine retourne un array (selon version / hydration)
                $result[] = [
                    'player' => $row[0] ?? null,
                    'avg' => (float)($row['avgRating'] ?? 0),
                    'count' => (int)($row['reviewCount'] ?? 0),
                ];
            }
        }
        // Normalisation si on a eu des objets Player seulement
        foreach ($result as &$r) {
            if ($r['avg'] === 0.0 && $r['player'] instanceof Player) {
                // recalcul rapide au besoin
                $r['avg'] = $this->getAverageForPlayer($r['player']);
                $r['count'] = $this->getReviewCountForPlayer($r['player']);
            }
        }
        return $result;
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
