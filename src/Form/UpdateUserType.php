<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateUserType extends AbstractType
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $authorizationChecker = $options['authorization_checker'];
        $builder
            ->add('pseudo', TextType::class, [
                'required' => true,
                'label'=>'Pseudo : ',
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un pseudo"
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le pseudo doit contenir au minimum {{ limit }} caractères'
                    ])],
                'attr'=>['class'=>'form-control']
            ])
            //->add('roles')
            //->add('password')

            ->add('nom', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un nom"
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au minimum {{ limit }} caractères'
                    ])],
                'label'=>'Nom : ',
                'attr'=>['class'=>'form-control']
            ])

            ->add('prenom', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un prenom"
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le prenom doit contenir au minimum {{ limit }} caractères'
                    ])],
                'label'=>'Prenom : ',
                'attr'=>['class'=>'form-control']
            ])

            ->add('mail', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir une adresse e-mail"
                    ]),
                    new Email([
                        'message' => "Veuillez saisir une adresse mail valide"
                    ])
                ],
                'label'=>'Mail : ',
                'attr'=>['class'=>'form-control']
            ])

            ->add('photo', FileType::class, [
                'mapped' => false,
                'attr'=>['class'=>'form-control'],
            ])

            ->add('telephone', TextType::class, [
                'label'=>'Telephone : ',
                'attr'=>['class'=>'form-control']
            ])

            ->add('actif', CheckboxType::class, [
                'required' => true,
                'label'=>'Actif : ',
                'attr'=>['class'=>'form-control']
            ])

            ->add('administrateur', CheckboxType::class, [
                'required' => true,
                'label'=>'Administrateur : ',
                'attr'=>['class'=>'form-control']
            ])

            ->add('campus', EntityType::class, [
                'required' => true,
                'class'=> Campus::class,
                'choice_label'=>'nom',
                'attr'=>['class'=>'form-control'],
                'query_builder'=> function(CampusRepository $campusRepository){
                    $qb = $campusRepository->createQueryBuilder('c');
                    // $qb->addOrderBy('c.name', 'ASC');
                    return $qb;
                }
            ])

            //->add('sorties')
        ;
        if (!$authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder->remove('actif');
            $builder->remove('administrateur');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'required' => false,
            'authorization_checker' => null,
        ]);
    }
}
