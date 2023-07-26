<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckerController extends AbstractController
{
    #[Route('/lucker', name: 'app_lucker')]
    public function index(): Response
    {
        return $this->render('lucker/index.html.twig', [
            'controller_name' => 'LuckerController',
        ]);
    }
}
