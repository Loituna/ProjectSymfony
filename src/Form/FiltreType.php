<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Campus', EntityType::class, [
                'class' => Campus::class,
                'mapped' => false,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-control'],
                'required'=>false
            ])

//            ->add('organisateur', CheckboxType::class, [
//                'label' => 'Sortie dont je suis l\'organisateur/trice',
//                'required' => false,
//                'attr' => ['class' => 'form-control']
//            ])
            ->add('participant', CheckboxType::class, [
                'label' => 'Sortie à laquelle je suis inscrit/e',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('pasParticipant', CheckboxType::class, [
                'label' => 'Sortie à laquelle je ne suis pas inscrit/e',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
//            ->add('sortiefini', CheckboxType::class, [
//                'label' => 'Sortie passées',
//                'required' => false,
//                'attr' => ['class' => 'form-control']
//            ])
//            ->add('dateDebut', DateType::class, [
//                'label' => 'Date de début',
//                'widget' => 'single_text',
//                'format' => 'yyyy-MM-dd',
//                'required' => false,
//                'attr' => ['class' => 'form-control']
//
//            ])
//            ->add('dateLimite', DateType::class, [
//                'label' => 'Date de fin',
//                'widget' => 'single_text',
//                'format' => 'yyyy-MM-dd',
//                'required' => false,
//                'attr' => ['class' => 'form-control']
//            ])
//            ->add('recherche', SearchType::class, [
//                'label' => 'Recherche',
//                'required' => false,
//                'attr' => ['class' => 'form-control']
//            ])
            ->add('submit', SubmitType::class, [
                'label' => 'RECHERCHER',
                'attr' => ['class'=> 'btn btn-success m-2 ']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
