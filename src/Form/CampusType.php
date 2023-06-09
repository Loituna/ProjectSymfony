<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CampusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => "Ajouter un nouveau Campus",
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un nom"
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le nom du Campus doit contenir au minimum {{ limit }} caractères'
                    ])
                ],
                    'attr' => ['class'=>'form-control']

            ])
            ->add('save',SubmitType::class, [
                'label' => 'Valider',
                    'attr' => ['class'=> 'btn btn-success mt-2']
            ]

            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Campus::class,
            'required' => false,
        ]);
    }
}
