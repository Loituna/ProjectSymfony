<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {



    }

    public function createEtat(ObjectManager $manager)
    {
        $param = ['Créée', 'Ouverte',
'Clôturée', 'Activité en
cours', 'Passée', 'Annulée','Archivée'];

        for ($i=0; $i<7;$i++) {
            $etat = new Etat();
            $etat->setLibelle($param[$i]);
            $manager->persist($etat);
        }



}


}
