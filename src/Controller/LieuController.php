<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/lieu', name: 'lieu_')]
class LieuController extends AbstractController
{
    #[Route('/{id}', name: 'detailByVille', requirements: ['id' => '\d+'])]
    public function detailByVille(LieuRepository $lieuRepository,VilleRepository $villeRepository , int $id,Request $request, ): Response
    {
        //Affichage
        $ville = $villeRepository->find($id);
        $lieus=$lieuRepository->findBy(['ville'=>$id]);

        //Formulaire
        $newlieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class,$newlieu);

        //Traitement
        $lieuForm->handleRequest($request);
        if ($lieuForm->isSubmitted()&& $lieuForm->isValid()){
            $newlieu->setVille($ville);
            $lieuRepository->save($newlieu,true);
            $this->addFlash('success', 'Lieu ajoutée');

            return $this->redirectToRoute('lieu_detailByVille',['id' => $id]);



        }


        return $this->render('lieu/detailByVille.html.twig', [ 'lieus'=>$lieus , 'ville'=> $ville , 'lieuForm' => $lieuForm->createView()]);
    }
    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function deleteLieu(LieuRepository $lieuRepository,VilleRepository $villeRepository , int $id ): Response
    {


        $lieu=$lieuRepository->find($id);
        $idVille = $lieu->getVille()->getId();

        $lieuRepository->remove($lieu,true);
        return $this->redirectToRoute('lieu_detailByVille',['id'=> $idVille ]);
    }


}
