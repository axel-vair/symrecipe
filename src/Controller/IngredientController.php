<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\User;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class IngredientController extends AbstractController
{

    /**
     * We have injected IngredientRepository inside Index method then we display all date from this repository
     * @param IngredientRepository $repository
     * @return Response
     */
    #[Route('/ingredient', 'ingredient.index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    #[Route('/ingredient/nouveau', 'ingredient.new', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $manager
    ): Response
    {
        $ingredient = new Ingredient();

        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());
            $manager->persist($ingredient);
            $manager->flush();
            $this->addFlash(
                'success',
                'Votre ingrédient a bien été créé ! '
            );
        }

        return $this->render('pages/ingredient/new.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @param IngredientRepository $repository
     * @param int $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     *
     *  With Symfony 6.3 we can use MapEntity  instead of FindOneBy
     *  function edit(EntityManagerInterface $manager, Ingredient $ingredient)
     */
    #[Route('/ingredient/edition/{id}', 'ingredient.edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(IngredientRepository $repository,
                         int $id, Request $request,
                         EntityManagerInterface $manager,
                        Ingredient $ingredientEntity
    ): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }

        if($this->getUser()->getId() !== $ingredientEntity->getUser()->getId()){
            return $this->redirectToRoute('ingredient.index');
        }
        $ingredient = $repository->findOneBy(["id" => $id]);
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a bien été modifié ! '
            );
            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);

    }


    /**
     * @param IngredientRepository $repository
     * @param int $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     *
     * Method that delete the object with his ID.
     * With Symfony 6.3 we can use MapEntity  instead of FindOneBy
     */
    #[Route('/ingredient/suppression/{id}', 'ingredient.delete', methods: ['GET'])]
    public function delete(IngredientRepository $repository,
                           int $id,
                           EntityManagerInterface $manager) : Response
    {
        if(!$id){

            $this->addFlash(
                'success',
                'L\'ingrédient n\'a pas été trouvé !'
            );

            return $this->redirectToRoute('ingredient.index');
        }

        $ingredient = $repository->findOneBy(["id" => $id]);
        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre ingrédient a bien été supprimé !'
        );

        return $this->redirectToRoute('ingredient.index');
    }
}
