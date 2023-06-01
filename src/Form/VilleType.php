<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class VilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => "Ajouter une nouvelle nom de Ville",
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un nom"
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Le nom de la Ville doit contenir au minimum {{ limit }} caractères'
                    ])
                ],
                'attr' => ['class'=>'form-control']

            ])
            ->add('codePostal', TextType::class, [
                'required' => true,
                'label' => "Ajouter un nouveau Code Postal",
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un Code Postal"
                    ]),
                    new Regex([
                        'pattern' => '/^(?:0[1-9]|[1-8]\d|9[0-8])\d{3}$/',
                        'message' => 'Veuillez entrez un code postal Valide'
                    ])
                ],
                'attr' => ['class'=>'form-control']

            ])
            ->add('save',SubmitType::class, [
                'label' => 'Valider',
                'attr' => ['class'=> 'btn btn-success mt-2 ']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
