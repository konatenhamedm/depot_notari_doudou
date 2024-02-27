<?php

namespace App\Form;

use App\Entity\PaiementFrais;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaiementFraisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add(
                'montant',
                TextType::class,


                [
                    'label' => false,
                    'attr' => ['class' => 'input-money input-mnt'], 'empty_data' => '0',
                ]
            )
            ->add('path', FichierType::class, ['label' => false,  'doc_options' => $options['doc_options'], 'required' => $options['doc_required'] ?? true])
            ->add('date', DateType::class, [
                'label' => false, 'html5' => false, 'attr' => ['class' => 'no-auto skip-init has-datepicker'], 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'empty_data' => ''
            ])
            ->add('sens', ChoiceType::class, ['choices' => array_flip(PaiementFrais::Sens), 'attr' => ['class' => 'sens']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaiementFrais::class,
            'doc_required' => true,
            'allow_extra_fields' => true
        ]);
        $resolver->setRequired('doc_options');
        $resolver->setRequired('doc_required');
    }
}
