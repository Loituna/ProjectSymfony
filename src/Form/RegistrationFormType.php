<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;



class RegistrationFormType extends AbstractType
{
    //private $authorizationChecker;

//    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
//    {
//        $this->authorizationChecker = $authorizationChecker;
//    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
      //  $userRole = $this->authorizationChecker->isGranted('ROLE_ADMIN') ? 'admin' : 'user';

        $builder
            ->add('pseudo')
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('mail')

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'required' => true,
                'first_options' => ['label' => 'Mot de passe', 'attr' => ['class' => 'form-control mb-3', 'style'=> 'width: 600px;']],
                'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => ['class' => 'form-control mb-3', 'style'=> 'width: 600px;']],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                ],

            ])

            ->add('campus', EntityType::class, [
                'class'=> Campus::class,
                'choice_label'=>'nom',
                'query_builder'=> function(CampusRepository $campusRepository){
                $qb = $campusRepository->createQueryBuilder('c');
               // $qb->addOrderBy('c.name', 'ASC');
                return $qb;
                }
            ])

            ->add('actif')
            ->add('administrateur')

            ->add('photo', FileType::class, [
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'required' => false
        ]);
    }
}
