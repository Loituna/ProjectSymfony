<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
//        $this->addLieu($manager);
//        $manager->flush();
    }

    public function addLieu(ObjectManager $manager){
        $generator = Factory::create('fr_FR');
        for($i=0;$i<30;$i++){
            $villeLieu = $manager->find(Ville::class,$generator->numberBetween(1,20) );

            $lieu = new Lieu();

            $lieu->setNom($generator->word)
                ->setRue($generator->streetAddress)
                ->setVille($villeLieu);

            $manager->persist($lieu);
        }

    }
}
