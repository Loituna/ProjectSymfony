<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AjoutSortieType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class SortieController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SortieRepository $sortieRepository): Response
    {
        //renvoyer une liste/tableau des sorties rentrées dans ma BDD
//        $listSorties = $sortieRepository->findAll();

        $listSorties = $sortieRepository->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.organisateur', 'o')
            ->addSelect('s.nom')
            ->addSelect('s.dateDebut')
            ->addSelect('s.dateLimite')
            ->addSelect('s.duree')
            ->addSelect('s.nbInscriptionMax')
            ->addSelect('e.libelle as etat')
            ->addSelect('COUNT(p.id) as participant_count')
            ->addSelect('o.nom as organisateur')
            ->groupBy('s.id')
            ->getQuery()
            ->getResult();

        return $this->render('main/index.html.twig', [
            'sorties' => $listSorties
        ]);
    }

    #[Route('/sortie/add', name:'sortie_add')]
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

    #[Route('/sortie/lieux-par-ville/', name: 'sortie_lieux_par_ville', methods: ['POST'])]
    public function lieuxParVille(Request $request, LieuRepository $lieuRepository){

        $patate = $request->getContent();
        $patate= (int)substr($patate,-2);

        $lieux = $lieuRepository->findLieuxByVille($patate);

        return $this->json($lieux,200,[],['groups'=>'lieu_data']);




    }

    #[Route('/sortie/{id}', name: 'sortie_show')]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}
