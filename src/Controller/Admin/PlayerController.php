<?php

namespace App\Controller\Admin;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/player')]
class PlayerController extends AbstractController
{
    #[Route('/', name: 'admin_player_index')]
    public function index(PlayerRepository $repo): Response
    { return $this->render('admin/player/index.html.twig', ['players'=>$repo->findAll()]); }

    #[Route('/new', name: 'admin_player_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    { $player = new Player(); return $this->handleForm($player,$request,$em); }

    #[Route('/{id}/edit', name: 'admin_player_edit')]
    public function edit(Player $player, Request $request, EntityManagerInterface $em): Response
    { return $this->handleForm($player,$request,$em); }

    private function handleForm(Player $player, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('photoFile')->getData();
            if ($file) {
                $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads/players';
                if (!is_dir($uploadsDir)) { @mkdir($uploadsDir, 0775, true); }
                $safeName = uniqid('p_').'.'.$file->guessExtension();
                $file->move($uploadsDir, $safeName);
                $player->setPhotoFilename($safeName);
            }
            $em->persist($player); $em->flush();
            $this->addFlash('success', 'Joueur enregistré');
            return $this->redirectToRoute('admin_player_index');
        }
        return $this->render('admin/player/form.html.twig', [ 'form'=>$form->createView(), 'player'=>$player ]);
    }

    #[Route('/{id}/delete', name: 'admin_player_delete', methods:['POST'])]
    public function delete(Player $player, Request $request, EntityManagerInterface $em): Response
    { if($this->isCsrfTokenValid('del_player_'.$player->getId(), $request->request->get('_token'))){ $em->remove($player); $em->flush(); $this->addFlash('success','Supprimé'); } return $this->redirectToRoute('admin_player_index'); }
}
