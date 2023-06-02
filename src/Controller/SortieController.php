<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AjoutSortieType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function add(EntityManagerInterface $entityManager, LieuRepository $lieuRepository, VilleRepository $villeRepository, Request $request): Response{
        $sortie = new Sortie();

        //créer un formulaire pour Sortie
        $sortieForm = $this->createForm(AjoutSortieType::class,$sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            //Effectuer les opérations nécessaires avec l'entité sortie
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie bien rajoutée');

            //Rediriger l'utilisateur vers une autre page, pour l'instant même page
            return $this->redirectToRoute('sortie_add');

            //Pour plus tard qd on aura fait la page de détails de la Sortie
//            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }
        return $this->render('sortie/addSortie.html.twig', [
           'sortieForm'=>$sortieForm->createView()
        ]);
    }

    #[Route('/lieux-par-ville/{villeId}', name: 'lieux_par_ville', methods: ['GET'])]
    public function lieuxParVille(int $villeId, LieuRepository $lieuRepository){
        $lieux = $lieuRepository->findLieuxByVille($villeId);
        //

        //Générez le HTMl pour les options de lieu
        $lieuxOptions = '';
        foreach ($lieux as $lieu){
            $lieuxOptions.='<option value="' . $lieu->getId() . '">' . $lieu->getNom() . '</option>';
        }
        return new Response($lieuxOptions);
    }

    #[Route('/sortie/{id}', name: 'sortie_show')]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}
