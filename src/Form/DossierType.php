<?php

namespace App\Form;

use App\Entity\Conservation;
use App\Entity\Dossier;
use App\Entity\Enregistrement;
use App\Entity\PaiementFrais;
use App\Entity\Type;
use App\Service\Omines\Column\NumberFormatColumn;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DossierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etape = $options['etape'];
        if (!$options['etape']) {
            $builder

                ->add('numeroOuverture', null, ['label' => 'Numéro d\'ouverture'])
                ->add('numeroRepertoire', null, ['label' => 'Numéro répertoire'])
                ->add('description', TextareaType::class, ['label' => 'Description'])
                ->add(
                    'montantTotal',
                    TextType::class,

                    ['label' => 'Montant total', 'attr' => ['class' => 'input-money input-mnt'], 'empty_data' => '0',]
                )
                //->add('montantTotal', TextType::class, ['attr' => ['class' => 'input-money input-mnt']])
                ->add('conservation', EntityType::class, [
                    'required' => true,
                    'class' => Conservation::class,
                    'choice_attr' => function (Conservation $typeClient) {
                        return ['data-code' => $typeClient->getCode()];
                    },
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.id', 'DESC');
                    },
                    'label' => 'Conservation',
                    'attr' => ['class' => 'form-control has-select2'],
                    'choice_label' => 'libelle',

                ])
                ->add('dateCreation', DateType::class, [
                    'label' => "Date de création", 'html5' => false, 'attr' => ['class' => 'no-auto skip-init'], 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'empty_data' => date('d/m/Y')
                ])
                ->add('objet', null, ['label' => 'Objet']);
        }


        if ($etape == 'signature') {
            $builder->add('documentSignes', CollectionType::class, [
                'entry_type' => DocumentSigneType::class,
                'entry_options' => [
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'doc_required' => $options['doc_required']
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,

            ]);
        }





        if ($etape == 'identification') {
            $builder->add('identifications', CollectionType::class, [
                'entry_type' => IdentificationType::class,
                'entry_options' => [
                    'label' => false
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,

            ]);
        }

        if ($etape == 'remise_acte') {
            $builder->add('remiseActes', CollectionType::class, [
                'entry_type' => RemiseActeType::class,
                'entry_options' => [
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'doc_required' => $options['doc_required']
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,

            ]);
        }


        if ($etape == 'redaction') {
            $builder->add('redactions', CollectionType::class, [
                'entry_type' => RedactionType::class,
                'entry_options' => [
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'doc_required' => $options['doc_required']
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,

            ]);
        }

        if ($etape == 'obtention') {
            $builder->add('obtentions', CollectionType::class, [
                'entry_type' => ObtentionType::class,
                'entry_options' => [
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'doc_required' => $options['doc_required']
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,

            ]);
        }


        if ($etape == 'remise') {
            $builder->add('remises', CollectionType::class, [
                'entry_type' => RemiseType::class,
                'entry_options' => [
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'doc_required' => $options['doc_required']
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,

            ]);
        }

        if ($etape == 'piece') {
            $builder->add('pieces', CollectionType::class, [
                'entry_type' => PieceType::class,
                'entry_options' => [
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'doc_required' => $options['doc_required']
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,
            ])->add(
                'montantAcheteur',
                TextType::class,


                ['label' => 'Montant à payer acheteur', 'attr' => ['class' => 'input-money input-mnt acheteur-input'], 'empty_data' => '0',]
            )
                ->add(
                    'montantVendeur',
                    TextType::class,

                    ['label' => 'Montant à payer vendeur', 'attr' => ['class' => 'input-money input-mnt vendeur-input'], 'empty_data' => '0',]
                );
        }


        if ($etape == 'enregistrement') {
            $builder->add('enregistrements', CollectionType::class, [
                'entry_type' => EnregistrementType::class,
                'entry_options' => [
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'doc_required' => $options['doc_required']
                ],
                'delete_empty' => function (Enregistrement $enregistrement) {
                    return null === $enregistrement || (!$enregistrement->getNumero());
                },
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,

            ]);
        }

        if ($etape == 'paiement') {
            $builder->add('paiementFrais', CollectionType::class, [
                'entry_type' => PaiementFraisType::class,
                'entry_options' => [
                    'label' => false,
                    'doc_options' => $options['doc_options'],
                    'doc_required' => $options['doc_required']
                ],
                /* 'delete_empty' => function (PaiementFrais $paiementFrais) {
                    return null === $paiementFrais || (!$paiementFrais->getId());
                }, */
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,

            ]);
        }


        if ($etape == 'classification') {
            $builder->add('infoClassification', InfoClassificationType::class);;
        }

        if ($etape == 'enregistrement') {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
                $data = $event->getData();

                /** @var FormInterface */
                $form = $event->getForm();

                foreach ($data['enregistrements'] as &$enregistrement) {
                    if (!isset($enregistrement['client'])) {
                        $enregistrement['client'] = false;
                    }
                }

                $event->setData($data);
            });
        }


        if ($etape) {
            if ($etape == 'classification' &&  ($options['current_etape'] != 'termine')) {
                $builder->add('cloture', SubmitType::class, ['label' => 'Archiver', 'attr' => ['class' => 'btn btn-dark btn-ajax']]);
            } else {

                if (($etape == snake_case($options['current_etape'])) || !$options['current_etape']) {
                    $builder->add('next', SubmitType::class, ['label' => 'Valider étape', 'attr' => ['class' => 'btn btn-primary btn-ajax']]);
                }
            }

            $builder->add('save', SubmitType::class, ['label' => 'Sauvegarder', 'attr' => ['class' => 'btn btn-success btn-ajax']]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dossier::class,
            'doc_required' => false,
            'doc_options' => [],
            'etape' => null,
            'current_etape' => null
        ]);

        $resolver->setRequired('etape');
        $resolver->setRequired('doc_required');
        $resolver->setRequired('doc_options');
        $resolver->setRequired('current_etape');
    }
}
