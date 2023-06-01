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
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                'attr' => ['class' =>'form-control']
            ])

            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date et Heure de la sortie:',
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                ]
            ])
            ->add('dateLimite', DateType::class, [
                'widget' => 'choice',
                'label' => "Date limite d'inscription: ",
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ]
            ])
            ->add('duree', IntegerType::class, [
                'label' =>'Durée: ',
                'attr' => ['class' =>'form-control']
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre de places: ',
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Il doit y avoir minimum {{ limit }} participants !'
                    ])
                ],
                'attr' => ['class' =>'form-control']
            ])



            ->add('ville', EntityType::class, [
                'required'=>true,
                'mapped'=>false,
                'label'=> 'Ville: ',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'choices' => $options['villes']->findAll(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir une ville'
                    ])
                ],
                'query_builder'=>function(VilleRepository $villeRepository){
                    $qb=$villeRepository->createQueryBuilder('s');
                    $qb->addOrderBy('s.nom', 'ASC');
                    return $qb;
                }
            ])

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();


                // Vérifier si la ville est sélectionnée
                if (isset($data['ville'])) {
                    $villeId = $data['ville']->getId();
                    $lieuOptions = [];

                    // Récupérer le repository des lieux depuis les options du formulaire
                    $lieuRepository = $options['lieux'];

                    // Récupérer les lieux correspondants à la ville sélectionnée
                    $lieux = $lieuRepository->findLieuxByVille($villeId);

                    // Créer les nouvelles options pour le champ lieu
                    foreach ($lieux as $lieu) {
                        $lieuOptions[$lieu->getNom()] = $lieu;
                    }

                    //configuration du champ lieu
                    $form->add('lieu', EntityType::class, [
                        'required' => false,
                        'label' => 'Lieu: ',
                        'class' => Lieu::class,
                        'placeholder' => 'Sélectionnez un lieu ',
                        'choice_label' => 'nom',
//                        'choices' => $lieuOptions,
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Veuillez choisir un lieu'
                            ])
                        ]
                    ]);
                }
            })



            ->add('save',SubmitType::class, [
                    'label' => 'Valider',
                        'attr' => ['class'=> 'btn btn-success mt-2']
                ])

        ;


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['lieux','villes']);
        $resolver->setAllowedTypes('lieux',LieuRepository::class);
        $resolver->setAllowedTypes('villes', VilleRepository::class);

        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
