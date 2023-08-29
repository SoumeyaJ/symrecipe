<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserController extends AbstractController
{
   /**
    * This controller allow us to edit user's profile
    *
    * @param User  $choosenUser
    * @param Request $request
    * @param EntityManagerInterface $manager
    * @return Response
    */
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(
        User $choosenUser, 
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher
        ): Response
    { //dd($user);
        // vérifier s'il utilisateur est connecté, cas contraire redirection vers la page de connexion
    //     if(!$this->getUser()) {
    //         return $this->redirectToRoute('security.login');
    //     }
    //    //dd($user, $this->getUser());

    //     // vérifier s'il s'agit bien du même utilisateur qui se connecte et non pas un autre qui veut modifier le profil,
    //     // cas contraire redirection vers la page des recettes
    //     if($this->getUser() !== $user) {
    //          return $this->redirectToRoute('recipe.index');
    //     }
        
        //création du formulaire
        $form = $this->createForm(UserType::class,  $choosenUser);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid( $choosenUser, $form->getData()->getPlainPassword())) {
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

      /**
     * This Controller allow us to edit the user's password
     *
     * @param User  $choosenUser
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
    #[Route('/utilisateur/edition-mot-de-passe/{id}', name: 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(
        User  $choosenUser, 
        Request $request, 
        UserPasswordHasherInterface $hasher, 
        EntityManagerInterface $manager ): Response
    {
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            if ($hasher->isPasswordValid($choosenUser, $form->getData()['plainPassword'])) {
                $choosenUser->setPassword(
                    $hasher->hashPassword($choosenUser, $form->getData() ['newPassword'])
                );
///////////////// cette méthode fait appel au "preUpdate" qui ne se déclenche pas, donc plan B changer pour "setPassword"//////////
                // $user->setPlainPassword(
                //     $form->getData()['newPassword']
                // );

                $manager->persist($choosenUser);
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

