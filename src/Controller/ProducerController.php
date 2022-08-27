<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Producer;
use App\Repository\ProducerRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProducerController extends AbstractController
{
    public function clean ($str) {
        $str = trim($str);
        $str = htmlspecialchars($str);
        $str = stripslashes($str);
        return $str;
    }

    public function check(string $email, ProducerRepository $producerRepository)
    {
        return $producerRepository->findOneBy(['email' => $email]);
    }

    public function authenticate(string $email, $pass, ProducerRepository $producerRepository)
    {
        $producer = $producerRepository->findOneBy(['email' => $email]);
        if ($producer)
            return password_verify($pass, $producer->getPassword()) ? $producer->getId():-1;
        return -1;
    }

    public function register($tab, $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $producer = new Producer();
        $producer->setName($tab[0]);
        $producer->setEmail($tab[1]);
        $producer->setPassword(password_hash($tab[3], PASSWORD_DEFAULT));

        // tell Doctrine you want to (eventually) save the Producer (no queries yet)
        $entityManager->persist($producer);

        // actually executes the queries (i.e. the INSERT query)
        return [($entityManager->flush()) ? false : true, $producer->getId()];
    }

    #[Route('/producer/products', name: 'producer-products')]
    public function producerProducts(CategoryRepository $catRepo, ProductRepository $prodRepo, Request $req) : Response
    {
        $producerId = $req->getSession()->get('producerId');
        if (!isset($producerId)) return $this->redirectToRoute('homepage');
        $categories = (new CategoryController())->fetchCategories($catRepo);

        $prodManager = new ProductController();
        $products = $prodManager->fetchProducts($prodRepo, ['status' => 0]);
        return $this->render('producer/products.html.twig', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    #[Route('/producer/add-product', name: 'producer-add-product')]
    public function addProduct(CategoryRepository $catRepo, ProductRepository $prodRepo, Request $req) : Response
    {
        $producerId = $req->getSession()->get('producerId');
        if (!isset($producerId)) return $this->redirectToRoute('homepage');
        $categories = (new CategoryController())->fetchCategories($catRepo);
        $units = ['kg', 'litre'];

        $prodManager = new ProductController();
        $products = $prodManager->fetchProducts($prodRepo, ['status' => 0]);
        return $this->render('producer/addProduct.html.twig', [
            'categories' => $categories,
            'products' => $products,
            'units' => $units,
        ]);
    }

    #[Route('/producer/addProductData', name: 'producer-add-product-data')]
    public function addProductData() : JsonResponse
    {
        $err = '';
        $name = $this->clean($_POST['name']);
        $name_err = empty($name) ? 'Veuillez remplir ce champ':'';

        $cat = $this->clean($_POST['cat']);
        $cat_err = (empty($cat) || !preg_match("/^\d+$/", $cat)) ? 'Veuillez choisir une catégorie':'';

        $price = $this->clean($_POST['price']);
        $price_err = (empty($price) || !preg_match("/^\d+$/", $price)) ? 'Prix invalide':'';

        $unit = $this->clean($_POST['unit']);
        $unit_err = empty($unit) ? 'Veuillez choisir une unité de mesure':'';

        $qty = $this->clean($_POST['qty']);
        $qty_err = (empty($qty) || !preg_match("/d*(?:\.\d+)?/", $qty)) ? 'Suivez le format 50,5':'';

        $img_err = isset($_FILES['image']['error'])? '':'Problème survenu sur le fichier';
        $res ='';
        return new JsonResponse(['0' => $err, '1' => $name_err, '2' => $cat_err, '3' => $price_err, '4' => $unit_err, '5' => $qty_err, '6' => $img_err, 'res' => $res]);
    }
}
