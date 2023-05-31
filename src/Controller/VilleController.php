<?php

namespace App\Controller;

use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/ville', name: 'ville_')]
class VilleController extends AbstractController
{
    #[Route('/{page}', name: 'list',requirements:["page"=> "\d+"])]
    public function list(VilleRepository $villeRepository, int $page=1): Response
    {
        $nbVille = $villeRepository->count([]);
        $maxPage = ceil($nbVille/VilleRepository::MAX_RESULT);
        if($page<1){
            return $this->redirectToRoute('ville_list',['page'=>1]);
        }
        elseif($page>$maxPage){
            return $this->redirectToRoute('ville_list',['page'=>$maxPage]);

        }else{
            $villes=$villeRepository->findVilleWithPagination($page);
          //  $villes=$villeRepository->findAll();

            return $this->render('ville/list.html.twig', ['villes'=> $villes, 'currentPage' => $page , 'maxPage'=> $maxPage

            ]);
    }}}

