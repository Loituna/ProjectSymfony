<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/ville', name: 'ville_')]
class VilleController extends AbstractController
{
    #[Route('/{page}', name: 'list',requirements:["page"=> "\d+"])]
    public function list(VilleRepository $villeRepository,Request $request, int $page=1): Response
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

            $newVille = new Ville();
            $villeForm = $this->createForm(VilleType::class,$newVille);


            $villeForm->handleRequest($request);
            if ($villeForm->isSubmitted()&& $villeForm->isValid()){



                $villeRepository->save($newVille,true);


                $this->addFlash('success', 'Ville ajoutée');
                return $this->redirectToRoute('ville_list');

            }

            return $this->render('ville/list.html.twig',
                ['villes'=> $villes,
                 'currentPage' => $page,
                 'maxPage'=> $maxPage,
                 'villeForm'=> $villeForm->createView()

            ]);
        }
    }
    #[Route('/delete/{id}', name : 'delete', requirements: ['id' => '\d+'])]
    public function delete(int $id, VilleRepository $villeRepository ){
        $ville = $villeRepository->find($id);

        //suppression de la série
        $villeRepository->remove($ville, true);

        $this->addFlash('success', $ville->getNom()." a été supprimé ! ");
        return $this->redirectToRoute('ville_list');
    }


}

