<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function fetchProducts(ProductRepository $producerRepository, $criteria = null, $orderBy = null, $limit = null, $offset = null)
    {
        return $producerRepository->findBy($criteria, $orderBy, $limit, $offset);        
    }

    public function addProduct($tab, $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setName($tab[0]);
        $product->setEmail($tab[1]);
        $product->setPassword(password_hash($tab[3], PASSWORD_DEFAULT));

        // tell Doctrine you want to (eventually) save the product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        return [($entityManager->flush()) ? false : true, $product->getId()];
    }
}
