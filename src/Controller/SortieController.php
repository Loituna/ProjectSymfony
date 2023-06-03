<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
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
        $listSorties = $sortieRepository->findEventsIndex();

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

    #[Route('/eventRegister/{sortieId}', name:'eventRegister', requirements: ['id'=>'\d+'])]
    public function registerToEvent(int $sortieId, UserRepository $userRepository, SortieRepository $sortieRepository):Response {

        //Récupèrer l'utilisateur courant
        $currentUser = $userRepository->find($this->getUser());

        //Récupèrer l'événement à partir de l'id
        $sortie = $sortieRepository->find($sortieId);

        //vérifier si l'événement existe
        if (!$sortie){
            throw $this->createNotFoundException('L événement n existe pas ');
        }

        //Vérifier si l'utilisateur est déjà inscrit à cet événement
        $isAlreadyParticipant = $sortie->getParticipants()->contains($currentUser);
        if ($isAlreadyParticipant){
            throw $this->createNotFoundException('Vous etes deja inscrit ! ');
        }

        //Ajout de l'utilisateur courant aux participants de l'événement
        $sortie->addUser($currentUser);

        //Enregistrer les modifications dans la base de données
        $sortieRepository->save($sortie, true);

        $this->addFlash('success', 'Vous êtes bien inscrit à '.$sortie->getNom());
        return $this->redirectToRoute('index');

    }

    #[Route('/eventRemove/{sortieId}', name:'eventRemove', requirements: ['id'=>'\d+'])]
    public function removeToEvent(int $sortieId, UserRepository $userRepository, SortieRepository $sortieRepository):Response {

        //Récupèrer l'utilisateur courant
        $currentUser = $userRepository->find($this->getUser());

        //Récupèrer l'événement à partir de l'id
        $sortie = $sortieRepository->find($sortieId);

        //vérifier si l'événement existe
        if (!$sortie){
            throw $this->createNotFoundException('L événement n existe pas ');
        }

        //Ajout de l'utilisateur courant aux participants de l'événement
        $sortie->removeUser($currentUser);

        //Enregistrer les modifications dans la base de données
        $sortieRepository->save($sortie, true);

        $this->addFlash('success', 'Vous êtes bien désinscrit de '.$sortie->getNom());
        return $this->redirectToRoute('index');

    }



}
