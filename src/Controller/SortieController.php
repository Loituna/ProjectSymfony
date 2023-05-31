<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\AjoutSortieType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name:'sortie_')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('sortie/addSortie.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    #[Route('/add', name:'add')]
    public function add(SortieRepository $sortieRepository): Response{
        $sortie = new Sortie();

        //créer un formulaire pour Sortie
        $sortieForm = $this->createForm(AjoutSortieType::class,$sortie);


        return $this->render('sortie/addSortie.html.twig', [
           'sortieForm'=>$sortieForm->createView()
        ]);
    }

}
