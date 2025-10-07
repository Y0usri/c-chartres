<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'admin_category_index')]
    public function index(CategoryRepository $repo): Response
    {
        return $this->render('admin/category/index.html.twig', [ 'categories' => $repo->findAll() ]);
    }

    #[Route('/new', name: 'admin_category_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category); $em->flush();
            $this->addFlash('success','Catégorie créée');
            return $this->redirectToRoute('admin_category_index');
        }
        return $this->render('admin/category/form.html.twig', ['form'=> $form->createView(), 'category'=>$category]);
    }

    #[Route('/{id}/edit', name: 'admin_category_edit')]
    public function edit(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->flush(); $this->addFlash('success','Modifié'); return $this->redirectToRoute('admin_category_index'); }
        return $this->render('admin/category/form.html.twig', ['form'=> $form->createView(), 'category'=>$category]);
    }

    #[Route('/{id}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('del_cat_'.$category->getId(), $request->request->get('_token'))) {
            $em->remove($category); $em->flush(); $this->addFlash('success','Supprimé');
        }
        return $this->redirectToRoute('admin_category_index');
    }
}
