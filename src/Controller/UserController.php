<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Tools\Uploader;
use Doctrine\ORM\EntityManagerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    #[Route('/liste', name: 'list')]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('user/list.html.twig', [
           'users' => $users
    ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id'=>'\d+'])]
    public function show(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        Request $request,
        AuthorizationCheckerInterface $authorizationChecker,
        Uploader $uploader): Response
    {
        //On récupère l'utilisateur avec son id
        $user = $userRepository->find($id);

        //Création du formulaire avec comme parametre authorizationChecker
        $userForm = $this->createForm(RegistrationFormType::class, $user,[
            'authorization_checker' => $authorizationChecker,
        ]);

        //On récupère le formulaire
        $userForm->handleRequest($request);

        //soumission du formulaire et vérification de sa validité
        if ($userForm->isSubmitted() && $userForm->isValid()) {

            //hashage du password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('plainPassword')->getData()
                )
            );
            /**
             * @var UploadedFile $file
             */
            $file = $userForm->get('photo')->getData();

            if($file){

                $newFileName= $uploader->saveFile($file,$user->getPseudo().'-'.$user->getCampus()->getNom().'-'.$user->getId(),$this->getParameter('upload_photo_user'));

                $user->setPhoto($newFileName);

            }

            //Enregistrer les modifications dans la base de données
            $userRepository->save($user,true);

            return $this->redirectToRoute('user_show', ['id'=> $id]);

        }

        return $this->render('user/update.html.twig',[
           'userForm' => $userForm->createView()
        ]);

    }

    #[IsGranted ('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(int $id, UserRepository $userRepository){
        $user = $userRepository->find($id);

        $userRepository->remove($user, true);

        $this->addFlash('success', $user->getPseudo()." a été supprimé !");

        return $this->redirectToRoute('user_list');
    }

    #[Route('/isActif/{id}', name: 'isActif')]
    public function isActif(User $user, UserRepository $userRepository): Response
    {
        //On récupère la data du champ actif
        if ($user->getActif() == false) {
            //On set à true ou a false selon son etat
            $user->setActif(true);
        } else {
            $user->setActif(false);
        }

        $this->addFlash('success', 'Modification pris en compte.');

        //Enregistrer les modifications dans la base de données
        $userRepository->save($user, true);

        return $this->redirectToRoute('user_list');
    }

}

