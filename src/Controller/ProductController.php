<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;
use Zebra_Image;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function fetchProducts(ProductRepository $producerRepository, $criteria = null, $orderBy = null, $limit = null, $offset = null)
    {
        return $producerRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function fetchOne(ProductRepository $producerRepository, int $id)
    {
        return $producerRepository->find($id);
    }

    public function addProduct($tab, $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setCategoryId($tab[0]);
        $product->setName($tab[1]);
        $product->setDescription($tab[2]);
        $product->setPrice($tab[3]);
        $product->setQuantity($tab[4]);
        $product->setNumberOfSales($tab[5]);
        $product->setImage($tab[6]);
        $product->setStatus($tab[7]);
        $product->setUnit($tab[8]);
        $product->setProducerId($tab[9]);

        // tell Doctrine you want to (eventually) save the product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        return [($entityManager->flush()) ? false : true, $product->getId()];
    }

    public function resizer($src, $target) {    
        $image = new Zebra_Image();

        $image->auto_handle_exif_orientation = false;

        $image->source_path = $src;

        $image->target_path = $target;

        $image->jpeg_quality = 100;

        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->handle_exif_orientation_tag = true;

        return $image->resize(330, 330, ZEBRA_IMAGE_CROP_CENTER);
    }
}
