<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'index.blog')]
    public function index(ArticleRepository $repository, Request $request, PaginatorInterface $paginator): Response
    {
        $articles = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/article/{slug}', name: 'show.article')]
    public function article(Article $article): Response
    {
        return $this->render('pages/blog/show.html.twig', [
            'article' => $article,
        ]);
    }
}
