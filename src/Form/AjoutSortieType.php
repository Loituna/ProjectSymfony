<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AjoutSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => "Nom de la Sortie",
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un nom"
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom de la Sortie doit contenir minimum {{ limit }} caractères ! '
                    ])
                ],
                    'attr' => ['class' =>'form-control']
            ])

            ->add('infoSortie', TextareaType::class, [
                'label' => "Informations sur la sortie",
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Maximum de {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('dateDebut', DateTimeType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                ],
            ])
            ->add('dateLimite', DateType::class, [
                'widget' => 'choice',
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ],
            ])
            ->add('duree', TextType::class)
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Combien de personnes maximum peuvent participer à cette sortie ?',
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Il doit y avoir minimum {{ limit }} participants !'
                    ])
                ]
            ])
            ->add('etat')
            ->add('campus')
            ->add('lieu')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
