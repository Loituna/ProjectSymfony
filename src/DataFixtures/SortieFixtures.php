<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

    }


    public function createSortie(ObjectManager $manager){
        $generator = Factory::create('fr_FR');


        for  ($i=0;$i<30;$i++){


        }


    }
}
