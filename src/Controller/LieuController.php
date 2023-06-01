<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/lieu', name: 'lieu_')]
class LieuController extends AbstractController
{
    #[Route('/{id}', name: 'detailByVille')]
    public function detailByVille(LieuRepository $lieuRepository,VilleRepository $villeRepository , int $id ): Response
    {
        $ville = $villeRepository->find($id);
        $lieus=$lieuRepository->findBy(['ville'=>$id]);


        return $this->render('lieu/detailByVille.html.twig', [ 'lieus'=>$lieus , 'ville'=> $ville

        ]);


    }

}
