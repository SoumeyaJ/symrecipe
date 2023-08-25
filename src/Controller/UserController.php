<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserController extends AbstractController
{
   /**
    * This controller allow us to edit user's profile
    *
    * @param User $user
    * @param Request $request
    * @param EntityManagerInterface $manager
    * @return Response
    */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    { //dd($user);
        // vérifier s'il utilisateur est connecté, cas contraire redirection vers la page de connexion
        if(!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
       //dd($user, $this->getUser());

        // vérifier s'il s'agit bien du même utilisateur qui se connecte et non pas un autre qui veut modifier le profil,
        // cas contraire redirection vers la page des recettes
        if($this->getUser() !== $user) {
             return $this->redirectToRoute('recipe.index');
        }
        
        //création du formulaire
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()->getPlainPassword())) {
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();
    
                $this->addFlash(
                    'success',
                    'Les informations de votre compte ont bie été modifiées'
                );
                return $this->redirectToRoute('recipe.index');
        }else {
            $this->addFlash(
                'warning',
                'Le mot de passe renseigné est incorrect.'
            );
        }
    }
        return $this->render('pages/user/edit.html.twig', [
             'form' => $form->createView(),    
        ]);
    }

    #[Route('/utilisateur/edition-mot-de-passe/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager ): Response
    {
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])) {
                $user->setPassword(
                    $hasher->hashPassword($user, $form->getData() ['newPassword'])
                );
///////////////// cette méthode fait appel au "preUpdate" qui ne se déclenche pas, donc plan B changer pour "setPassword"
                // $user->setPlainPassword(
                //     $form->getData()['newPassword']
                // );

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Le mot de passe de votre compte a été modifié'
                );
                return $this->redirectToRoute('recipe.index');
            }
            else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView(),    
       ]);
    }
}

