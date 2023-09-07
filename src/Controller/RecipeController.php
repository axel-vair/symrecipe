<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\User;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class RecipeController extends AbstractController
{

    #[Route('/recette', name: 'recipe.index', methods: ['GET'])]
    public function index(PaginatorInterface $paginator,
                          RecipeRepository   $repository,
                          Request            $request
    ): Response
    {

        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes
        ]);
    }

    #[Route('/recette/creation', 'recipe.new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $manager
    ): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a bien été créée !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/recette/edition/{id}', 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(RecipeRepository $repository,
                         int $id, Request $request,
                         EntityManagerInterface $manager,
                        Recipe $recipeEntity
    ): Response
    {

        if(!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }

        if($this->getUser()->getId() !== $recipeEntity->getUser()->getId()){
            return $this->redirectToRoute('recipe.index');
        }

        $recipe = $repository->findOneBy(["id" => $id]);
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a bien été modifiée ! '
            );
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/recette/suppression/{id}', 'recipe.delete', methods: ['GET'])]
    public function delete(RecipeRepository $repository,
                           int $id,
                           EntityManagerInterface $manager) : Response
    {
        if(!$id){

            $this->addFlash(
                'success',
                'La recette n\'a pas été trouvé !'
            );

            return $this->redirectToRoute('recipe.index');
        }


        $recipe = $repository->findOneBy(["id" => $id]);
        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre recette a bien été supprimée !'
        );

        return $this->redirectToRoute('recipe.index');
    }

    #[Route('/recette/{id}', 'recipe.show', methods: ['GET'])]
    public function show(Recipe $recipe,
                         Request $request) : Response {

        if(!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }
        if($this->getUser()->getId() !== $recipe->getUser()->getId()){
            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }

}
