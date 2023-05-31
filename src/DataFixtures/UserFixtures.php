<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher=$hasher;
    }


    public function load(ObjectManager $manager): void
    {


    }

    /**
     * @param ObjectManager $manager
     * @return Nothing
     * Fonction dont le but est d'ajouter un pull Utilisateur à la BDD.
     * Les deux premiers paramètres de la premiere itération sont à completer par vos soins
     * pour pouvoir ensuite vous connecter en tant qu'admin sur votre site internet.
     */
    public function addUser(ObjectManager $manager) {
        $generator = Factory::create('fr_FR');

        $userAdmin = new User();


        // Ici Vos Parametres pour votre Admin
        $userAdmin->setPseudo("AdminPatate");
        $userAdmin->setPassword($this->hasher->hashPassword($userAdmin, 'Patate'));

        $campusAdmin = $manager->find(Campus::class,1);


        $userAdmin->setRoles(['ROLE_ADMIN']);
        $userAdmin->setNom($generator->lastName)
            ->setPrenom($generator->firstName)
            ->setMail($generator->email)
            ->setActif('true')
            ->setAdministrateur('true')
            ->setTelephone($generator->phoneNumber)
            ->setCampus($campusAdmin);


        $manager->persist($userAdmin);


        // Ici le pull pour les utilisateurs lambda




        for  ($i=0;$i<20;$i++){
            $user = new User();
            $campus = $manager->find(Campus::class,$generator->numberBetween(1,4));

            if($i <= 15){
                $user->setRoles(['ROLE_USER'])
                    ->setAdministrateur(false);
            }else {
                $user->setRoles(['ROLE_ADMIN'])
                    ->setAdministrateur(true);
            }

           $user->setPrenom($generator->firstName)
                ->setNom($generator->lastName)
                ->setPseudo($user->getPrenom().'.'.$user->getNom())
                ->setMail(      $user->getPseudo()
                                    .'@campus-eni'.
                                    $generator->numberBetween(2009,2023)
                                    .'.fr')
                ->setCampus($campus)
                ->setTelephone($generator->phoneNumber)
                ->setActif((bool)$generator->randomElements(['true', 'false']))
                ->setPassword($this->hasher->hashPassword($user, $generator->password));



            $manager->persist($user);


        }




}


}
