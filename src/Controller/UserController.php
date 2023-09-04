<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(UserRepository $repository,
                         int $id,
                         Request $request,
                         EntityManagerInterface $manager) : Response
    {
        $user = $repository->find($id);
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre profil a bien été modifié ! '
            );

            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}

     // TODO : Found how to do the same with User entiry on parameter and getUser(). It's not working because of $user not found
        /*
            public function edit(User $user){
        if(!$this->getUser()){
        return $this->redirectToRoute('security.login');
        }
        if($this->getUser() === $user){
        return $this->redirectToRoute('recipe.index');
        }

         */