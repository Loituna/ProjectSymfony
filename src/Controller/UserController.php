<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/', name: 'show')]
    public function show(): Response
    {

        //$user = $userRepository->find($id);

        $user = $this->getUser();

        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    public function update(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        Request $request): Response
    {
        $user = $userRepository->find($id);
        $userForm = $this->createForm(RegistrationFormType::class, $user);
        $userForm->handleRequest($request);


        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_show');

        }

        return $this->render('user/update.html.twig',[
           'userForm' => $userForm->createView()
        ]);

    }

}
