<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\AdminRepository;
use App\Repository\CarrierRepository;
use App\Repository\ConsumerRepository;
use App\Repository\ProducerRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PersonController extends AbstractController
{
    public function clean ($tab) {
        foreach ($tab as $key => $value) {
            $tab[$key] = trim($value);
            $tab[$key] = htmlspecialchars($value);
            $tab[$key] = stripslashes($value);
        }
        return $tab;
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('person/login.html.twig', [
            'controller_name' => 'PersonController',
        ]);
    }

    #[Route('/loginData', name: 'loginData', methods: ['POST'])]
    public function loginData(AdminRepository $ad, CarrierRepository $car, ConsumerRepository $cons, ProducerRepository $prod, Request $req)
    {
        $tab = $_POST['loginData'];
        $tab = $this->clean($tab);
        $email_err = ''; $pass_err = ''; $err = ''; $res = false; $route = '';
        $email_err = (empty($tab[0])) ? 'Veuillez remplir ce champ' : '';
        $pass_err = (empty($tab[1])) ? 'Veuillez remplir ce champ' : '';

        if (empty($email_err) && empty($pass_err)) {
            $ret = (new AdminController)->authenticate($tab[0], $tab[1], $ad);
            if ($ret != -1) {
                $req->getSession()->set('adminId', $ret);
                $res = true; $route = 'admin/products';
            }

            $ret = (new ConsumerController)->authenticate($tab[0], $tab[1], $cons);
            if ($ret != -1) {
                $req->getSession()->set('consumerId', $ret);
                $res = true; $route = 'consumer/products';
            }

            $ret = (new ProducerController)->authenticate($tab[0], $tab[1], $prod);
            if ($ret != -1) {
                $req->getSession()->set('producerId', $ret);
                $res = true; $route = 'producer/products';
            }

            $ret = (new CarrierController)->authenticate($tab[0], $tab[1], $car);
            if ($ret != -1) {
                $req->getSession()->set('carrierId', $ret);
                $res = true; $route = 'carrier/products';
            }
            $err = ($res)?'':'Identifiants incorrects';
        }
        return new JsonResponse(['0' => $err, '1' => $email_err, '2' => $pass_err, 'res' => $res, 'route' => $route]);
    }

    #[Route('/register-consumer', name: 'registerConsumer')]
    public function registerConsumer(): Response
    {
        return $this->render('person/registerConsumer.html.twig', [
            'controller_name' => 'PersonController',
        ]);
    }

    #[Route('/registerConsumerData', name: 'registerConsumerData', methods: ['POST'])]
    public function registerConsumerData(AdminRepository $admin, CarrierRepository $carrier, ConsumerRepository $consumer, ProducerRepository $producer, ManagerRegistry $manager, Request $req): JsonResponse
    {
        $tab = $_POST['registerConsumerData'];
        $tab = $this->clean($tab);
        $cons = new ConsumerController();
        $err=''; $res = '';
        $name_err = empty($tab[0])? 'Veuillez remplir ce champ' : '';
        
        if (empty($tab[1])) $email_err = 'Veuillez remplir ce champ';
        else if (!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/", $tab[1]))
            $email_err = 'Adresse email invalide';
        else if (
            (new AdminController())->check($tab[1], $admin) != null ||
            $cons->check($tab[1], $consumer) != null ||
            (new CarrierController())->check($tab[1], $carrier) != null ||
            (new ProducerController())->check($tab[1], $producer) != null
        ) $email_err = 'Cette adresse existe déjà. Veuillez vous connecter';
        else $email_err = '';

        $pass_err = (empty($tab[2])) ? 'Veuillez remplir ce champ' : '';
        $confirm_err = empty($tab[3])? 'Veuillez remplir ce champ':'';
        $confirm_err = ($tab[2] != $tab[3])? 'Les mots de passe sont différents':'';

        if (empty($name_err) && empty($email_err) && empty($pass_err) && empty($confirm_err)) {
            try {
                $return = $cons->register($tab, $manager);
                if ($return[0]) {
                    $err = ''; $res = 'Inscription réussie';
                    $session = $req->getSession();
                    $session->set('consumerId', $return[1]);
                    return new JsonResponse(['0' => $err, '1' => $name_err, '2' => $email_err, '3' => $pass_err, '4' => $confirm_err, 'bool' => true, 'res' => $res]);
                } else $err = 'Un problème est survenu'; $res = '';
            } catch (Exception $e) {
                $err = 'Un problème est survenu'; $res = '';
            }
        }
        return new JsonResponse(['0' => $err, '1' => $name_err, '2' => $email_err, '3' => $pass_err, '4' => $confirm_err, 'bool' => false, 'res' => $res]);
    }

    #[Route('/register-producer', name: 'registerProducer')]
    public function registerProducer(): Response
    {
        return $this->render('person/registerProducer.html.twig', [
            'controller_name' => 'PersonController',
        ]);
    }

    #[Route('/registerProducerData', name: 'registerProducerData', methods: ['POST'])]
    public function registerProducerData(AdminRepository $admin, CarrierRepository $carrier, ConsumerRepository $consumer, ProducerRepository $producer, ManagerRegistry $manager, Request $req): JsonResponse
    {
        $tab = $_POST['registerProducerData'];
        $tab = $this->clean($tab);
        $prod = new ProducerController();
        $name_err=''; $email_err=''; $pass_err=''; $confirm_err=''; $err=''; $res = '';
        $name_err = empty($tab[0])? 'Veuillez remplir ce champ' : '';
        
        if (empty($tab[1])) $email_err = 'Veuillez remplir ce champ';
        else if (!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/", $tab[1]))
            $email_err = 'Adresse email invalide';
        else if (
            (new AdminController())->check($tab[1], $admin) != null ||
            ($prod->check($tab[1], $producer) != null) ||
            (new CarrierController())->check($tab[1], $carrier) != null ||
            (new ConsumerController())->check($tab[1], $consumer) != null
        ) $email_err = 'Cette adresse existe déjà. Veuillez vous connecter';
        else $email_err = '';

        $pass_err = (empty($tab[2])) ? 'Veuillez remplir ce champ' : '';
        $confirm_err = empty($tab[3])? 'Veuillez remplir ce champ':'';
        $confirm_err = ($tab[2] != $tab[3])? 'Les mots de passe sont différents':'';

        if (empty($name_err) && empty($email_err) && empty($pass_err) && empty($confirm_err)) {
            try {
                $return = $prod->register($tab, $manager);
                if ($return[0] == true) {
                    $err = ''; $res = 'Inscription réussie';
                    $session = $req->getSession();
                    $session->set('producerId', $return[1]);
                    return new JsonResponse(['0' => $err, '1' => $name_err, '2' => $email_err, '3' => $pass_err, '4' => $confirm_err, 'bool' => true, 'res' => $res]);
                } else $err = 'Un problème est survenu'; $res = '';
            } catch (Exception $e) {
                $err = 'Un problème est survenu'; $res = '';
            }
        }
        return new JsonResponse(['0' => $err, '1' => $name_err, '2' => $email_err, '3' => $pass_err, '4' => $confirm_err, 'bool' => false, 'res' => $res]);
    }

    #[Route('/', name: 'homepage')]
    #[Route('/user/products', name: 'user-products')]
    public function userProducts(ProductRepository $prod, CategoryRepository $cat): Response
    {
        $prodManager = new ProductController();
        return $this->render('person/products.html.twig', [
            'controller_name' => 'PersonController',
            'categories' => (new CategoryController)->fetchCategories($cat),
            'products' => $prodManager->fetchProducts($prod, ['status' => 1])
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $req)
    {
        $session = $req->getSession()->clear();
        return $this->redirectToRoute('login');
    }
}
