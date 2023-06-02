<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AjoutSortieType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;


class SortieController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SortieRepository $sortieRepository, Security $security): Response
    {
        $currentUser = $security->getUser();
        $sortiesUserInscrit = $sortieRepository->findSortiesByCurrentUser($currentUser);

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
            'sorties' => $listSorties,
            'sortiesUserInscrit' => $sortiesUserInscrit
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

    #[Route('/sortie/lieux-par-ville/{villeId}', name: 'sortie_lieux_par_ville', methods: ['GET'])]
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
