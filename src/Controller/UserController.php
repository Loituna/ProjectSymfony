<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/update/{id}', name: 'update',  requirements: ['id'=>'\d+'])]
    public function update(
        int $id,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        Request $request,
        AuthorizationCheckerInterface $authorizationChecker): Response
    {
        $user = $userRepository->find($id);
        $userForm = $this->createForm(RegistrationFormType::class, $user,[
            'authorization_checker' => $authorizationChecker,
        ]);
        $userForm->handleRequest($request);


        if ($userForm->isSubmitted() && $userForm->isValid()) {

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('plainPassword')->getData()
                )
            );

            $userRepository->save($user,true);

            return $this->redirectToRoute('user_show');

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
}
