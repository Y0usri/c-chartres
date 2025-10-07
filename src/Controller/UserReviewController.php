<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserReviewController extends AbstractController
{
    #[Route('/my/reviews', name: 'user_reviews')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        $reviews = [];
        if ($user instanceof \App\Entity\User) {
            $reviews = $user->getReviews();
        }
        return $this->render('user/reviews.html.twig', [ 'reviews' => $reviews ]);
    }
}
