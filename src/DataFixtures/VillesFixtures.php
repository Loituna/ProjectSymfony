<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class VillesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

    }

    public function addVille(ObjectManager $manager){

        $generator = Factory::create('fr_FR');

        for ($i=0; $i<20; $i++ ){
            $ville = new Ville();
            $ville
                ->setNom($generator->city)
                ->setCodePostal($generator->numberBetween(10000,90000));

            $manager->persist($ville);
        }
        $manager->flush($ville);
    }

}
