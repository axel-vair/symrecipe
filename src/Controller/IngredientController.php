<?php

namespace App\Controller;

use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IngredientController extends AbstractController
{

    /**
     * We have injected IngredientRepository inside Index method then we display all date from this repository
     * @param IngredientRepository $repository
     * @return Response
     */
    #[Route('/ingredient', name: 'app_ingredient')]

    public function index(IngredientRepository $repository): Response
    {
        return $this->render('/pages/ingredient/index.html.twig', [
            'ingredients' => $repository->findAll()
        ]);
    }
}
