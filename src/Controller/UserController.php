<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
}
