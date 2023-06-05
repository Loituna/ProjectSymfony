<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class
AjoutSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => "Nom de la Sortie",
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un nom:"
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom de la Sortie doit contenir minimum {{ limit }} caractères ! '
                    ])
                ],
                    'attr' => ['class' =>'form-control']
            ])

            ->add('infoSortie', TextareaType::class, [
                'label' => "Description et infos:",
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Maximum de {{ limit }} caractères'
                    ])
                ],
                'attr' => ['class' =>'form-control mb-2']
            ])

            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et Heure de la sortie:',
                //on mets une callback pour la contrainte. voir la function 'dateValide' en dessous
                'constraints' => [
                     new Callback([$this, 'dateValide']),
                ],
            ])
            ->add('dateLimite', DateType::class, [
                'widget' => 'single_text',
                'label' => "Date limite d'inscription: ",
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ]

            ])
            ->add('duree', TextType::class, [
                'label' =>'Durée: ',
                'attr' => ['class' =>'form-control']
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre de places: ',
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 2,
                        'message' => 'Il doit y avoir minimum {{ compared_value }} participants !'
                    ])
                ],
                'attr' => ['class' =>'form-control']
            ])



            ->add('ville', EntityType::class, [
                'mapped'=>false,
                'label'=> 'Ville: ',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir une ville'
                    ])
                ],
                'attr' => ['class' =>'form-control']
            ])

            ->add('lieu', ChoiceType::class, [
                'label' => 'Lieu: ',
                'placeholder' => 'Lieu (Choisir une ville',
                'required' => false,
                'attr' => ['class' =>'form-control']
            ])

            ->add('save',SubmitType::class, [
                    'label' => 'Enregistrer le brouillon',
                        'attr' => ['class'=> 'btn btn-success mt-2']
                ])

            ->add('publish',SubmitType::class, [
                'label' => 'Publier',
                'attr' => ['class'=> 'btn btn-primary mt-2']
            ])
        ;


        $formModifier = function (FormInterface $form, Ville $ville){


//            $lieux = $ville->getLieux(); //mais si c'est null ca ne marche pas, donc ternaire
            $lieux = (null == $ville) ? [] : $ville->getLieux(); //soit tableau vide car null, soit lieux de la ville
            $form->add('lieu', EntityType::class, [

                'class' => Lieu::class,
                'choices' => $lieux,
                'choice_label' => 'nom',
                'placeholder' => 'Lieu (Choisir une ville',
                'label' => 'Lieu : ',
                'required' => false
            ]);
        };
//dd($formModifier);


        $builder->get('ville')->addEventListener(
          FormEvents::POST_SUBMIT, //on recupere dans event l'evenement

            function (FormEvent $event) use ($formModifier)
            {
              $ville = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $ville);
            }
        );


    }

    public function configureOptions(OptionsResolver $resolver): void
    {

        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }

    public function dateValide($value, ExecutionContextInterface $executionContext){
        //$value représente la valeur du champ 'date debut'
        $dateDebut = $value;

        // nous utilisons $executioncontexte pour accéder à l'ensemble du formulaire et obtenir la valeur du champ dateLimiteInscription
        $dateLimitInscription = $executionContext->getRoot()->get('dateLimite')->getData();

        if ($dateDebut <= $dateLimitInscription){
            //ici on créer une violation si la date de début est inférieur à la date d'inscription
            $executionContext->buildViolation("La date limite pour participer à la sortie est supérieur à la date du début de l'événement.")
                ->atPath('dateDebut')
                ->addViolation();
        }

    }
}
