<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use App\Entity\Review;
use App\Form\ReviewType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/player')]
class PlayerController extends AbstractController
{
    #[Route('/{id}', name: 'app_player_show', requirements: ['id' => '\\d+'])]
    public function show(Player $player, Request $request, EntityManagerInterface $em, PlayerRepository $playerRepository): Response
    {
        $form = null;
        $existing = null;
        $user = $this->getUser();
        // Log simple de diagnostic (en dev) via monolog channel par défaut
        if ($request->isMethod('POST')) {
            // On peut aussi dump dans flash pour retour immédiat si besoin
            // $this->addFlash('info', 'DEBUG POST reçu (token?)');
        }
        if ($user) {
            // Recherche directe pour éviter doublons et garantir cohérence DB
            $existing = $em->getRepository(Review::class)->findOneBy([
                'user' => $user,
                'player' => $player,
            ]);
            if (!$existing) {
                // Pré-affecter user/player AVANT validation pour que UniqueEntity fonctionne
                $review = new Review();
                $review->setUser($user)->setPlayer($player);
                $form = $this->createForm(ReviewType::class, $review);
                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        try {
                            $em->persist($review);
                            $em->flush();
                            $this->addFlash('success', 'Avis ajouté.');
                            return $this->redirectToRoute('app_player_show', ['id' => $player->getId()]);
                        } catch (UniqueConstraintViolationException $e) {
                            $this->addFlash('warning', 'Vous avez déjà déposé un avis pour ce joueur.');
                            return $this->redirectToRoute('app_player_show', ['id' => $player->getId()]);
                        }
                    } else {
                        // Construire un message d'erreurs agrégé
                        $errors = [];
                        foreach ($form->getErrors(true) as $error) { $errors[] = $error->getMessage(); }
                        if ($errors) {
                            $this->addFlash('danger', 'Le formulaire contient des erreurs: '.implode(' | ', array_unique($errors)));
                        } else {
                            $this->addFlash('danger', 'Le formulaire n\'a pas pu être soumis.');
                        }
                    }
                }
            }
        }
        // moyenne optimisée via repository custom
        $avg = $playerRepository->getAverageRatingForPlayer($player->getId());
        // Récupération des avis triés récents -> anciens
        $reviews = $em->getRepository(Review::class)->findBy(['player' => $player], ['createdAt' => 'DESC']);
        return $this->render('player/show.html.twig', [
            'player' => $player,
            'average' => $avg,
            'review_form' => $form?->createView(),
            'has_review' => (bool)$existing,
            'reviews' => $reviews,
        ]);
    }
}
