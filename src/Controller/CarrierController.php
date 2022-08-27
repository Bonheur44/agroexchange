<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Carrier;
use App\Repository\CarrierRepository;
use Symfony\Component\Routing\Annotation\Route;

class CarrierController extends AbstractController
{
    public function check(string $email, CarrierRepository $carrierRepository)
    {
        return $carrierRepository->findOneBy(['email' => $email]);
    }

    public function authenticate(string $email, $pass, CarrierRepository $carrierRepository)
    {
        $carrier = $carrierRepository->findOneBy(['email' => $email]);
        if ($carrier)
            return password_verify($pass, $carrier->getPassword()) ? $carrier->getId():-1;
        return -1;
    }
}
