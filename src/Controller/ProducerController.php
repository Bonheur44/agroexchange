<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Producer;
use Doctrine\Persistence\ManagerRegistry;
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
    public function addProductData(Request $req, ManagerRegistry $doctrine) : JsonResponse
    {
        $product = new ProductController();
        $err = ''; $res = '';
        $name = $this->clean($_POST['name']);
        $name_err = empty($name) ? 'Veuillez renseignez un nom':'';

        $cat = $this->clean($_POST['cat']);
        $cat_err = (empty($cat) || !preg_match("/^\d+$/", $cat)) ? 'Veuillez choisir une catégorie':'';

        $price = $this->clean($_POST['price']);
        $price_err = (empty($price) || !preg_match("/^\d+$/", $price)) ? 'Prix invalide':'';

        $unit = $this->clean($_POST['unit']);
        $unit_err = empty($unit) ? 'Veuillez choisir une unité de mesure':'';

        $qty = $this->clean($_POST['qty']);
        $qty_err = (empty($qty) || !preg_match("/d*(?:\.\d+)?/", $qty)) ? 'Suivez le format 50,5':'';

        $img = isset($_FILES['image'])?$_FILES['image']:null;
        $img_err = ($img != null)? '':'Problème survenu sur le fichier';

        $desc = $this->clean($_POST['desc']);
        $desc_err = empty($desc) ? 'Veuillez décrire votre produit':'';

        if (empty($name_err) && empty($cat_err) && empty($price_err) && empty($unit_err) &&empty($name_err) && empty($qty_err) && empty($img_err) && empty($desc_err)) {
            $fileInfo = pathinfo($img['name']);
            $extension = $fileInfo['extension'];
            $producerId = $req->getSession()->get('producerId');
            $fileName = '../public/products/' . $producerId . date('.d-m-y.h-i-s.') . $extension;
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($extension, $allowedExtensions)) {
                try {
                    move_uploaded_file($img['tmp_name'], $fileName);
                    if ($product->resizer($fileName, $fileName)) {
                        $res = $product->addProduct([$cat, $name, $desc, $price, $qty, 0, str_replace('../public', '', $fileName), 0, $unit, $producerId], $doctrine)?
                        'Produit ajouté avec succès':'';
                    } else $img_err = "Problème survenu sur le fichier";
                } catch(Exception $e) {
                    $img_err = "Problème survenu sur le fichier";
                }
            }
        }

        return new JsonResponse(['0' => $err, '1' => $name_err, '2' => $cat_err, '3' => $price_err, '4' => $unit_err, '5' => $qty_err, '6' => $img_err, '7' => $desc_err, 'res' => $res]);
    }

    #[Route('/producer/product-details/{id}', name: 'producer-product-details')]
    public function productDetails(int $id, CategoryRepository $catRepo, ProductRepository $prodRepo, Request $req) : Response
    {
        $producerId = $req->getSession()->get('producerId');
        if (!isset($producerId)) return $this->redirectToRoute('homepage');
        $categories = (new CategoryController())->fetchCategories($catRepo);

        $prodManager = new ProductController();
        $product = $prodManager->fetchOne($prodRepo, $id);
        if ($product == null) return $this->redirectToRoute('producer-products');
        else if ($product->getProducerId() != $producerId) return $this->redirectToRoute('producer-products');
        $relatedProducts = $prodRepo->related($product->getCategoryId(), $product->getId());
        return $this->render('producer/product-details.html.twig', [
            'categories' => $categories,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
