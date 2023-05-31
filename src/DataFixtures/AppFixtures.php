<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture


{
private CampusFixtures $campus;
private EtatFixtures $etat;
private VillesFixtures $ville;
private SortieFixtures $sortie;
private UserFixtures $user;
private LieuFixtures $lieu;


    public function __construct(CampusFixtures $campus,EtatFixtures $etat,VillesFixtures $ville,SortieFixtures $sortie,UserFixtures $user,LieuFixtures $lieu, )
    {
        $this->lieu=$lieu;
        $this->campus=$campus;
        $this->ville=$ville;
        $this->user=$user;
        $this->etat=$etat;
        $this->sortie=$sortie;




    }
    public function load(ObjectManager $manager ): void
    {
        $this->campus->addCampus($manager);
        $this->etat->createEtat($manager);
        $this->ville->addVille($manager);
        $this->user->addUser($manager);
        $this->lieu->addLieu($manager);

        $manager->flush();
        $this->sortie->createSortie($manager);
        $manager->flush();
    }
}
