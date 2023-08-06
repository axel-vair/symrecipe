<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipe', 'recipe.index')]
    public function index(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $receipts = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
        10
        );
        return $this->render('pages/recipe/index.html.twig', [
            'receipts' => $receipts
        ]);
    }
}
