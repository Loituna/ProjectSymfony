<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AjoutSortieType;
use App\Form\FiltreType;
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
use Symfony\Component\Validator\Constraints\Date;


class SortieController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SortieRepository $sortieRepository, Security $security, EtatRepository $etatRepository, Request $request): Response

        {
        $this->reloadEtat($etatRepository, $sortieRepository);
        $currentUser = $security->getUser();
        $eventsWhereUserParticipant = $sortieRepository->findSortiesByCurrentUser($currentUser);
        $listEvents = $sortieRepository->findEventsIndex();




        $filtreForm = $this->createForm(FiltreType::class);

        $filtreForm->handleRequest($request);


            if ($filtreForm->isSubmitted() && $filtreForm->isValid()){


               $listEvents = $sortieRepository->listeSortieFiltre($filtreForm);


            }


                return $this->render('main/index.html.twig', [
            'sorties' => $listEvents,
            'sortiesUserInscrit' => $eventsWhereUserParticipant,
            'filtreForm'=>$filtreForm->createView()
        ]);
    }

    #[Route('/sortie/add', name:'sortie_add')]
    public function add(EntityManagerInterface $entityManager,
                        LieuRepository $lieuRepository,
                        VilleRepository $villeRepository,
                        Request $request,
                        EtatRepository $etatRepository,
                        UserRepository $userRepository): Response{
        $event = new Sortie();

        //Récupèrer l'utilisateur courant
        $currentUser = $userRepository->find($this->getUser());

        //créer un formulaire pour Sortie
        $eventForm = $this->createForm(AjoutSortieType::class,$event);

        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()){
            $event = $eventForm->getData();

            if( $eventForm->get('save')->isClicked()){
                $state = $etatRepository->findOneBy(['libelle'=>'Créée']);
                $event->setEtat($state);
            } else if( $eventForm->get('publish')->isClicked()){
                $state = $etatRepository->findOneBy(['libelle'=>'Ouverte']);
                $event->setEtat($state);
            }else{
                return $this->redirectToRoute('sortie_add');
            }

            $event->setOrganisateur($this->getUser());
            // set le Campus de la sortie automatiquement au campus de l'organistateur
            $CampusCurrentUser = $currentUser->getCampus();
            $event->setCampus($CampusCurrentUser);

            //Effectuer les opérations nécessaires avec l'entité sortie
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie bien rajoutée');

            //Rediriger l'utilisateur vers une autre page, pour l'instant même page
            return $this->redirectToRoute('index');

            //Pour plus tard qd on aura fait la page de détails de la Sortie
//            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }
        return $this->render('sortie/addSortie.html.twig', [
           'sortieForm'=>$eventForm->createView()
        ]);
    }

    #[Route('/sortie/lieux-par-ville/', name: 'sortie_lieux_par_ville', methods: ['POST'])]
    public function lieuxParVille(Request $request, LieuRepository $lieuRepository){

        $patate = $request->getContent();
        $patate= (int)substr($patate,-2);

        $lieux = $lieuRepository->findLieuxByVille($patate);

        return $this->json($lieux,200,[],['groups'=>'lieu_data']);
    }

    #[Route('/sortie/{eventId}', name: 'sortie_show', requirements: ['id'=>'\d+'])]
    public function show(int $eventId, SortieRepository $sortieRepository ): Response
    {
        $sortie = $sortieRepository->find($eventId);
        $listParticipants = $sortie->getParticipants();


        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'listParticipants' =>$listParticipants
        ]);
    }

    #[Route('/eventRegister/{eventId}', name:'eventRegister', requirements: ['id'=>'\d+'])]
    public function registerToEvent(int $eventId, UserRepository $userRepository, SortieRepository $sortieRepository):Response {

        //Récupèrer l'utilisateur courant
        $currentUser = $userRepository->find($this->getUser());

        //Récupèrer l'événement à partir de l'id
        $event = $sortieRepository->find($eventId);

        //récupérer la date du jour dans une variable
        $date = new \DateTime('now');

        //vérifier si l'événement existe et qu'il n'est pas complet
        if (!$event || ($event->getParticipants()->count() == $event->getNbInscriptionMax() && $event->getNbInscriptionMax() != null )){
            $this->addFlash('error', "la sortie est complète où alors elle n'existe plus");
            return $this->redirectToRoute('index');
        } else if($event->getDateLimite() <= $date){
            $this->addFlash('error', "la Date Limite est dépassée, vous ne pouvez plus vous inscrire");
            return $this->redirectToRoute('index');
        }

        //Vérifier si l'utilisateur est déjà inscrit à cet événement
        $isAlreadyParticipant = $event->getParticipants()->contains($currentUser);
        if ($isAlreadyParticipant){
            $this->addFlash('error', "Vous êtes déjà inscrit à cet événement ! ");
            return $this->redirectToRoute('index');
        }

        //Ajout de l'utilisateur courant aux participants de l'événement
        $event->addUser($currentUser);

        //Enregistrer les modifications dans la base de données
        $sortieRepository->save($event, true);

        $this->addFlash('success', 'Vous êtes bien inscrit à '.$event->getNom());
        return $this->redirectToRoute('index');

    }

    #[Route('/eventRemove/{eventId}', name:'eventRemove', requirements: ['id'=>'\d+'])]
    public function removeToEvent(int $eventId, UserRepository $userRepository, SortieRepository $sortieRepository):Response {

        //Récupèrer l'utilisateur courant
        $currentUser = $userRepository->find($this->getUser());

        //Récupèrer l'événement à partir de l'id
        $event = $sortieRepository->find($eventId);

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
        return $this->redirectToRoute('sortie_show', ['eventId'=>$eventId]);
    }

    #[Route('/publishEvent/{eventId}', name:'sortie_publishEvent', requirements: ['id'=>'\d+'])]
    public function publishEvent(int $eventId, SortieRepository $sortieRepository, EtatRepository $etatRepository, Request $request){
        $eventToPublish = $sortieRepository->find($eventId);

        $date = new \DateTime('now');

        if ($eventToPublish->getDateDebut()<= $date){
            $this->addFlash('error', "Cet événement est censé avoir commencé, vous ne pouvez le publier ! ");
            return $this->redirectToRoute('sortie_show', ['id'=>$eventId]);
        }else {
            $eventToPublish->setEtat($etatRepository->find(2));
        }

        $sortieRepository->save($eventToPublish, true);
        return $this->redirectToRoute('sortie_show', ['eventId'=>$eventId]);
    }

    private function reloadEtat(EtatRepository $etatRepository, SortieRepository $sortieRepository)
    {
        $listSortie = $sortieRepository->findAll();

        foreach ($listSortie as $sortie ) {

            $dateNow = new \DateTime();

            if ($dateNow>$sortie->getDateDebut()&&$dateNow<$sortie->getDateLimite()){
                $sortie->setEtat(($etatRepository->find(3)));
            }

            if ($dateNow>$sortie->getDateDebut()){
                $sortie->setEtat($etatRepository->find(5));}


            if($dateNow->format('Y-m-d')==$sortie->getDateDebut()->format('Y-m-d')){

                $sortie->setEtat($etatRepository->find(4));}


            $dif = $dateNow->diff($sortie->getDateDebut());
            if ($dif->m > 1) {
                $sortie->setEtat($etatRepository->find(7));
            }


            $sortieRepository->save($sortie, true);
        }

    }


}
