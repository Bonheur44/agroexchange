<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Admin;
use App\Repository\AdminRepository;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function check(string $email, AdminRepository $adminRepository)
    {
        return $adminRepository->findOneBy(['email' => $email]);
    }

    public function authenticate(string $email, $pass, AdminRepository $adminRepository)
    {
        $admin = $adminRepository->findOneBy(['email' => $email]);
        if ($admin)
            return password_verify($pass, $admin->getPassword()) ? $admin->getId():-1;
        return -1;
    }
}
