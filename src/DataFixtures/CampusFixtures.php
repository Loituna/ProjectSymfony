<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->addCampus($manager);

    }

    public function addCampus(ObjectManager $manager){

        $nomsCampus = ["Rennes", "Nantes", "Quimper", "Niort"];

        for ($i=0; $i<4; $i++){
            $campus = new Campus();
            $campus -> setNom($nomsCampus[$i]);

            $manager->persist($campus);
        }

        $manager->flush($campus);
    }
}
