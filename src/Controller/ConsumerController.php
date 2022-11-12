<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Consumer;
use App\Repository\ConsumerRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Annotation\Route;

class ConsumerController extends AbstractController
{
    public function check(string $email, ConsumerRepository $consumerRepository)
    {
        return $consumerRepository->findOneBy(['email' => $email]);
    }

    public function authenticate(string $email, $pass, ConsumerRepository $consumerRepository)
    {
        $consumer = $consumerRepository->findOneBy(['email' => $email]);
        if ($consumer)
            return password_verify($pass, $consumer->getPassword()) ? $consumer->getId():-1;
        return -1;
    }

    public function register($tab, $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $consumer = new Consumer();
        $consumer->setName($tab[0]);
        $consumer->setEmail($tab[1]);
        $consumer->setPassword(password_hash($tab[3], PASSWORD_DEFAULT));

        // tell Doctrine you want to (eventually) save the Consumer (no queries yet)
        $entityManager->persist($consumer);

        // actually executes the queries (i.e. the INSERT query)
        return [($entityManager->flush()) ? false : true, $consumer->getId()];
    }

    #[Route('/consumer/products', name: 'consumer-products')]
    public function consumerProducts(CategoryRepository $catRepo, ProductRepository $prodRepo, Request $req) : Response
    {
        $consumerId = $req->getSession()->get('consumerId');
        if (!isset($consumerId)) return $this->redirectToRoute('user-products');
        $categories = (new CategoryController())->fetchCategories($catRepo);

        $prodManager = new ProductController();
        $products = $prodManager->fetchProducts($prodRepo, ['status' => 0]);
        return $this->render('consumer/products.html.twig', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    #[Route('/consumer/product-details/{id}', name: 'consumer-product-details')]
    public function productDetails(int $id, CategoryRepository $catRepo, ProductRepository $prodRepo, Request $req) : Response
    {
        $consumerId = $req->getSession()->get('consumerId');
        if (!isset($consumerId)) return $this->redirectToRoute('homepage');
        $categories = (new CategoryController())->fetchCategories($catRepo);

        $prodManager = new ProductController();
        $product = $prodManager->fetchOne($prodRepo, $id);
        if ($product == null) return $this->redirectToRoute('consumer-products');
        $relatedProducts = $prodRepo->related($product->getCategoryId(), $product->getId());
        return $this->render('consumer/product-details.html.twig', [
            'categories' => $categories,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
    // #[Route('/test/{id}')]
    // public function test($id, ManagerRegistry $doctrine)
    // {
    //     $entityManager = $doctrine->getManager();

    //     $repository = $doctrine->getRepository(Consumer::class);
    //     $consumer = $repository->find($id);

    //     $entityManager->remove($consumer);
    //     $entityManager->flush();
    // }
}
