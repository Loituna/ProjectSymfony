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




        for  ($i=0;$i<100;$i++){

            $bamboche = new Sortie();

            $bamboche->setNom($generator->randomElement(['Bowling', 'Cinéma', 'Flechette', 'Escalade', 'Piscine', 'Pintes & compagine', 'PMU', 'CTF avec Denis', 'Randonnée']))
                ->setInfoSortie($generator->realText(200))



                ->setDateDebut($generator->dateTimeBetween('-3 years' , '-1 week'))
                ->setDateLimite($generator->dateTimeBetween($bamboche->getDateDebut()))
                ->setDuree($generator->numberBetween(1,4).' jours')
                ->setNbInscriptionMax($generator->numberBetween(10,15))
                ->setOrganisateur( $manager->find(User::class,$generator->numberBetween(1,20)))
                ->setEtat( $manager->find(Etat::class,$generator->numberBetween(1,7)))
                ->setCampus($manager->find(Campus::class,$generator->numberBetween(1,4)))
                ->setLieu($manager->find(Lieu::class,$generator->numberBetween(1,4)));


                     for($j=0;$j<$bamboche->getNbInscriptionMax();$j++){

                    $bamboche->addUser($manager->find(User::class,$generator->numberBetween(1, 20)));
                   }

                    $manager->persist($bamboche);


        }


    }
}
