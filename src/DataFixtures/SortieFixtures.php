<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use App\Repository\LieuRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

    }


    public function createSortie(ObjectManager $manager, ){

        $generator = Factory::create('fr_FR');

        for ($i = 0; $i < 50; $i++) {
            $bamboche = new Sortie();

            $nom = $generator->randomElement(['Bowling', 'Cinéma', 'Fléchette', 'Escalade', 'Piscine', 'Pintes & Compagnie', 'PMU', 'CTF avec Denis', 'Randonnée']);
            $bamboche->setNom($nom)
                    ->setInfoSortie($generator->realText(200));

            // Générer la date de début et la date limite
            $dateDebut = $generator->dateTimeBetween('-2 months', '+9 months');
            $bamboche->setDateDebut($dateDebut);

            // Calculer la date limite d'inscription
            $dateLimite = clone $dateDebut;
            $dateLimite->modify("-" . $generator->numberBetween(1, 5) . " days");
            $bamboche->setDateLimite($dateLimite);

            // Calculer la durée en fonction de la date de début
            // Calculer la durée aléatoire en minutes
            $dureeMinutes = $generator->numberBetween(30, 180);
            $duree = $dureeMinutes . ' minutes';
            $bamboche->setDuree($duree);

            // Définir l'état en fonction des dates
            $aujourdhui = new \DateTime();
            if ($dateDebut == $aujourdhui && $aujourdhui <= $dateDebut->modify("+{$duree}")) {
                $etat = $manager->find(Etat::class, 1); // Activité en cours
            } elseif ($dateLimite < $aujourdhui && $dateDebut > $aujourdhui->modify("+{$duree}")) {
                $etat = $manager->find(Etat::class, 2); // Clôturée
            } elseif ($dateDebut < $aujourdhui->modify("+{$duree}")) {
                $etat = $manager->find(Etat::class, 3); // Passée
            } else {
                // Définir un état par défaut si nécessaire
                $etat = $manager->find(Etat::class, 1);
            }
            $bamboche->setEtat($etat);

            // Générer le reste des attributs de Sortie
            $bamboche->setNbInscriptionMax($generator->numberBetween(10, 15))
                ->setOrganisateur($manager->find(User::class, $generator->numberBetween(1, 20)))
                ->setCampus($manager->find(Campus::class, $generator->numberBetween(1, 4)))
                ->setLieu($manager->find(Lieu::class, $generator->numberBetween(1, 4)));

            // Générer les utilisateurs participants
            for ($j = 0; $j < $bamboche->getNbInscriptionMax(); $j++) {
                $bamboche->addUser($manager->find(User::class, $generator->numberBetween(1, 5)));
            }

            $manager->persist($bamboche);
        }


//        for  ($i=0;$i<50;$i++){
//
//            $bamboche = new Sortie();
//
//            $bamboche->setNom($generator->randomElement(['Bowling', 'Cinéma', 'Flechette', 'Escalade', 'Piscine', 'Pintes & compagine', 'PMU', 'CTF avec Denis', 'Randonnée']))
//                ->setInfoSortie($generator->realText(200))
//
//
//
//                ->setDateDebut($generator->dateTimeBetween('-3 years' , '-1 week'))
//                ->setDateLimite($generator->dateTimeBetween($bamboche->getDateDebut()))
//                ->setDuree($generator->numberBetween(1,4).' jours')
//                ->setNbInscriptionMax($generator->numberBetween(10,15))
//                ->setOrganisateur( $manager->find(User::class,$generator->numberBetween(1,20)))
//                ->setEtat( $manager->find(Etat::class,$generator->numberBetween(1,7)))
//                ->setCampus($manager->find(Campus::class,$generator->numberBetween(1,4)))
//                ->setLieu($manager->find(Lieu::class,$generator->numberBetween(1,4)));
//
//
//                     for($j=0;$j<$bamboche->getNbInscriptionMax();$j++){
//
//                    $bamboche->addUser($manager->find(User::class,$generator->numberBetween(1, 5)));
//                   }
//
//                    $manager->persist($bamboche);
//
//
//        }


    }
}
