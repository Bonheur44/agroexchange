<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    public function fetchCategories(CategoryRepository $categoryRepository)
    {
        return $categoryRepository->findAll();
    }

    public function check(CategoryRepository $categoryRepository, int $id)
    {
        return $categoryRepository->find($id);
    }

    // #[Route('/add-category')]
    // public function addCategory(ManagerRegistry $doctrine)
    // {
    //     $entityManager = $doctrine->getManager();

    //     $category = new Category();
    //     $category->setName("Légumes");

    //     $entityManager->persist($category);
    //     $entityManager->flush();

    //     return new Response('Saved category at: '.$category->getId());
    // }
}
