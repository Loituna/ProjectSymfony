<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
use Container7GF5p5a\getEtatRepositoryService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private Security $security)
    {
        parent::__construct($registry, Sortie::class);


    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findSortiesByCurrentUser($currentUser)
    {
        // Création de la requête avec createQueryBuilder
        $query = $this->createQueryBuilder('s')
            // Jointure avec la relation "participants"
            ->leftJoin('s.participants', 'p')
            // Condition pour filtrer les sorties où le current user est un participant
            ->andWhere('p = :currentUser')
            ->addSelect('s.nom')
            ->addSelect('s.id')
            // Définition du paramètre ":currentUser"
            ->setParameter('currentUser', $currentUser)

            // Récupération de la requête
            ->getQuery();

        $results = $query->getResult();
//        var_dump($results);
//        dd($query->getResult());
        // Exécution de la requête et retour du résultat
        return $query->getResult();
    }

    public function findEventsIndex()
    {
        $listSorties = $this->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.organisateur', 'o')
            ->addSelect('s.id')
            ->addSelect('s.nom')
            ->addSelect('s.dateDebut')
            ->addSelect('s.dateLimite')
            ->addSelect('s.duree')
            ->addSelect('s.nbInscriptionMax')
            ->addSelect('e.libelle as etatNom')
            ->addSelect('e.id as etatId')
            ->addSelect('COUNT(p.id) as participant_count')
            ->addSelect('o.nom as organisateurNom')
            ->addSelect('o.id as organisateurId')
            ->groupBy('s.id')
            ->getQuery()
            ->getResult();

        return $listSorties;
    }
//    /*O
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function listeSortieFiltre(FormInterface $filtreForm,)
    {

            $qb= $this->createQueryBuilder('s');




            if ($filtreForm->get('Campus')) {
                $qb->leftJoin('s.campus', 'c')
                    ->andWhere('s.campus = :campus')
                    ->setParameter('Campus', $filtreForm['Campus']);
            }
            if ($filtreForm->get('sortiefini')){
                $qb->leftJoin('s.etat', 'e')
                    ->andWhere('e = 5 ');
            }
            if ($filtreForm->get('participant')){
                $qb ->leftJoin('s.participants', 'p')
                    ->andWhere('s.participants = :user' )
                    ->setParameter('user', $this->security->getUser());
            }
            if ($filtreForm->get('pasParticipant')){
                $qb->andWhere('s.participants != :user')
                    ->setParameter('user', $this->security->getUser());

            }
            if ($filtreForm->get('organisateur')){
                $qb->leftJoin('s.organisateur', 'o')
                ->andWhere('s.organisateur = :user')
                ->setParameter('user', $this->security->getUser());
            }

       $listeSortie=  $qb->getQuery()->getResult();

            dd($listeSortie);



    }


}
