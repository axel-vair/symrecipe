<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class RecipeController extends AbstractController
{
    /**
     *
     * This method display recipes and paginate with PaginatorInterface
     * @param PaginatorInterface $paginator
     * @param RecipeRepository $repository
     * @param Request $request
     * @return Response
     */
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

    /**
     * This method displays public recipes
     * @param RecipeRepository $repository
     * @return Response
     */
    #[Route('/recette/publique', 'recipe.index.public', methods: ['GET'])]
    public function indexPublic(RecipeRepository $repository) : Response {

        $recipes = $repository->findBy(['isPublic' => true]);
        return $this->render('pages/recipe/publicRecipe.html.twig', [
            'recipes' => $recipes
        ]);
    }

    /**
     * This method allow users to create a new recipe
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
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

    /**
     *
     * This method allow users to edit their recipes
     * @param RecipeRepository $repository
     * @param int $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Recipe $recipeEntity
     * @return Response
     */
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

    /**
     *
     * This method allow users to delete their recipes
     * @param RecipeRepository $repository
     * @param int $id
     * @param EntityManagerInterface $manager
     * @return Response
     */
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


    /**
     * This method displays recipe on their unique page
     * @param Recipe $recipe
     * @param Request $request
     * @return Response
     */
    #[Route('/recette/{id}', 'recipe.show', methods: ['GET', 'POST'])]
    public function show(Recipe $recipe,
                         Recipe $recipeEntity,
                         Request $request,
                         MarkRepository $markRepository,
                         EntityManagerInterface $manager) : Response {

        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);
            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if(!$existingMark){
                $manager->persist($mark);
            }else{
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
            }
            $manager->flush();
            $this->addFlash(
                'success',
                'La note a bien été prise en compte.'
            );
            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }


        if(!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }

        if($this->getUser()->getId() !== $recipeEntity->getUser()->getId() && $recipeEntity->isIsPublic() === false){
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
    }

}
