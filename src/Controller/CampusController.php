<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use App\Tools\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CampusController extends AbstractController
{
    #[Route('/campus', name: 'app_campus')]
    public function index(CampusRepository $campusRepository,
                            Request $request,
                            EntityManagerInterface $entityManager): Response
    {
        //Renvoyer une liste/tableau des campus rentrés dans ma BDD
        $listCampus = $campusRepository->findAll();

        $campus = new Campus();
        //créer un formulaire pour Campus
        $campusForm = $this->createForm(CampusType::class,$campus);
        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()){
            $campusRepository->save($campus, true);

            $this->addFlash('success', 'Campus bien rajouté');

            return $this->redirectToRoute('app_campus');
        }
        return $this->render('campus/indexCampus.html.twig', [
            'campus' => $listCampus,
            'campusForm' =>$campusForm->createView()
        ]);
    }


}
