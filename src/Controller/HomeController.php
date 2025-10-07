<?php

namespace App\Controller;

use App\Repository\PlayerRepository;
use App\Repository\CategoryRepository;
use App\Repository\LevelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, PlayerRepository $playerRepository, CategoryRepository $categoryRepository, LevelRepository $levelRepository): Response
    {
        $page = (int)$request->query->get('page', 1);
        if ($page < 1) { $page = 1; }
        $limit = 12;

        $rawQ = $request->query->get('q');
        $rawCategory = $request->query->get('category');
        $rawLevel = $request->query->get('level');
        $rawMinAvg = $request->query->get('minAvg');

        $criteria = [
            'q' => ($rawQ !== null && $rawQ !== '') ? mb_substr(trim($rawQ),0,50) : null,
            'category' => (is_string($rawCategory) && ctype_digit($rawCategory)) ? (int)$rawCategory : null,
            'level' => (is_string($rawLevel) && ctype_digit($rawLevel)) ? (int)$rawLevel : null,
            'minAvg' => (is_string($rawMinAvg) && $rawMinAvg !== '' && is_numeric($rawMinAvg)) ? (float)$rawMinAvg : null,
        ];
        $result = $playerRepository->searchPaginated($criteria, $page, $limit);
        // Pour debug ponctuel: décommenter la ligne suivante si besoin
        // dump($criteria, $result['total']);
        $players = $result['data'];
        $ids = array_map(fn($p)=>$p->getId(), $players);
        $averages = $playerRepository->getAverageRatingsForPlayers($ids);
        $total = $result['total'];
        $pages = (int)ceil(max(1, $total)/$limit);
        $categories = $categoryRepository->findAll();
        $levels = $levelRepository->findAll();
        return $this->render('home/index.html.twig', [
            'players' => $players,
            'averages' => $averages,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'criteria' => $criteria,
            'categories' => $categories,
            'levels' => $levels,
        ]);
    }
}
