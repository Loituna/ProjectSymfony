<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AjoutSortieType;
use App\Repository\EtatRepository;
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
    public function add(EntityManagerInterface $entityManager, LieuRepository $lieuRepository, VilleRepository $villeRepository, Request $request, EtatRepository $etatRepository): Response{
        $sortie = new Sortie();

        //créer un formulaire pour Sortie
        $sortieForm = $this->createForm(AjoutSortieType::class,$sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $sortie = $sortieForm->getData();

            $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Ouverte']));
            $sortie->setOrganisateur($this->getUser());

            //Effectuer les opérations nécessaires avec l'entité sortie
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie bien rajoutée');

            //Rediriger l'utilisateur vers une autre page, pour l'instant même page
            return $this->redirectToRoute('index');

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

    #[Route('/sortie/{sortieId}', name: 'sortie_show', requirements: ['id'=>'\d+'])]
    public function show(int $sortieId, SortieRepository $sortieRepository ): Response
    {
        $sortie = $sortieRepository->find($sortieId);
        $listParticipants = $sortie->getParticipants();


        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'listParticipants' =>$listParticipants
        ]);
    }

    #[Route('/eventRegister/{sortieId}', name:'eventRegister', requirements: ['id'=>'\d+'])]
    public function registerToEvent(int $sortieId, UserRepository $userRepository, SortieRepository $sortieRepository):Response {

        //Récupèrer l'utilisateur courant
        $currentUser = $userRepository->find($this->getUser());

        //Récupèrer l'événement à partir de l'id
        $sortie = $sortieRepository->find($sortieId);

        //récupérer la date du jour dans une variable
        $date = new \DateTime('now');

        //vérifier si l'événement existe et qu'il n'est pas complet
        if (!$sortie || ($sortie->getParticipants()->count() == $sortie->getNbInscriptionMax() && $sortie->getNbInscriptionMax() != null )){
            $this->addFlash('error', "la sortie est complète où alors elle n'existe plus");
            return $this->redirectToRoute('index');
        } else if($sortie->getDateLimite() <= $date){
            $this->addFlash('error', "la Date Limite est dépassée, vous ne pouvez plus vous inscrire");
            return $this->redirectToRoute('index');
        }

        //Vérifier si l'utilisateur est déjà inscrit à cet événement
        $isAlreadyParticipant = $sortie->getParticipants()->contains($currentUser);
        if ($isAlreadyParticipant){
            $this->addFlash('error', "Vous êtes déjà inscrit à cet événement ! ");
            return $this->redirectToRoute('index');
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
        $event = $sortieRepository->find($sortieId);

        //vérifier si l'événement existe
        if (!$event){
            $this->addFlash('error', "la sortie semble ne plus exister");
            return $this->redirectToRoute('index');
        }

        //Ajout de l'utilisateur courant aux participants de l'événement
        $event->removeUser($currentUser);

        //Enregistrer les modifications dans la base de données
        $sortieRepository->save($event, true);

        $this->addFlash('success', 'Vous êtes bien désinscrit de '.$event->getNom());
        return $this->redirectToRoute('index');

    }

    #[Route('/cancelEvent/{eventId}', name:'sortie_cancelevent', requirements: ['id'=>'\d+'])]
    public function cancelEvent(int $eventId, SortieRepository $sortieRepository, EtatRepository $etatRepository, Request $request){
        $eventToCancel = $sortieRepository->find($eventId);

        $date = new \DateTime('now');
        $raisonAnnulation = $request->query->get('raison');

        if ($eventToCancel->getDateDebut()<= $date){
            $this->addFlash('error', "Cet événement a déjà commencé, vous ne pouvez l'annuler ! ");
            return $this->redirectToRoute('sortie_show', ['id'=>$eventId]);
        }else {
            $eventToCancel->setInfoSortie($raisonAnnulation);
            $eventToCancel->setEtat($etatRepository->find(6));
        }

        $sortieRepository->save($eventToCancel, true);
        return $this->redirectToRoute('sortie_show', ['sortieId'=>$eventId]);
    }

    #[Route('/updateEvent/{eventId}', name: 'update',  requirements: ['id'=>'\d+'])]
    public function updateEvent(
        int $eventId,
        SortieRepository $sortieRepository,
        Request $request)

    {
        //récupération de l'id de la sortie en cours
        $event = $sortieRepository->find($eventId);

        $sortieForm = $this->createForm(AjoutSortieType::class, $event);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){

            $debutSortie = $event->getDateDebut();
            $finSortie = $event->getDateLimite();

            if ($debutSortie>=$finSortie){
                $this->addFlash('error', "Le date du début de la sortie est supérieur à la date de fin de la sortie !");
            }

            $sortieRepository->save($event, true);

            return $this->redirectToRoute('sortie_show', ['sortieId' => $eventId]);

        }

        return $this->render('sortie/updateEvent.html.twig', [
            'sortieForm'=>$sortieForm->createView()
        ]);
    }

}
