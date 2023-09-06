<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     *
     * This controller allow edit profile (pseudo and fullname)
     * @param UserRepository $repository
     * @param int $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */

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
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(UserRepository $repository,
                         int $id,
                         Request $request,
                         EntityManagerInterface $manager,
                         UserPasswordHasherInterface $hasher,
                         User $userEntity) : Response

    {
        if(!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }

        if($this->getUser()->getId() !== $userEntity->getId()){
            return $this->redirectToRoute('security.login');
        }
        $user = $repository->find($id);
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($user, $form->getData()->getPlainPassword())){

                $user = $form->getData();

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre profil a bien été modifié ! '
                );

                return $this->redirectToRoute('recipe.index');
            }else{
                $this->addFlash(
                    'warning',
                    "Mot de passe incorrect. Votre profil n'a pas été modifié !"

                );
                return $this->redirectToRoute('recipe.index');
            }


        }
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/utilisateur/edition/mot-de-passe/{id}', name: 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(UserRepository $repository,
                                 int $id,
                                 Request $request,
                                 UserPasswordHasherInterface $hasher,
                                 EntityManagerInterface $manager,
                                User $userEntity
    ) : Response
    {

        if(!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }

        if($this->getUser()->getId() !== $userEntity->getId()){
            return $this->redirectToRoute('security.login');
        }
        $user = $repository->find($id);
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if($hasher->isPasswordValid($user, $form->getData()['plainPassword'])){
                $user->setPassword(
                    $hasher->hashPassword(
                        $user, $form->getData()['newPassword']
                    )

                );

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Le mot de passe a bien été modifié !"
                );
                return $this->redirectToRoute('recipe.index');
            }else{
                $this->addFlash(
                    'warning',
                    "Vous avez entré un mauvais mot de passe!"
                );
                return $this->redirectToRoute('recipe.index');
            }

        }

        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

