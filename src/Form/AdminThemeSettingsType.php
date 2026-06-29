<?php

namespace App\Form;

use App\Entity\SiteSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminThemeSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adminColorScheme', ChoiceType::class, [
                'label' => 'Mode d\'affichage',
                'choices' => SiteSetting::SCHEME_CHOICES,
                'help' => 'Le mode "Automatique" suit le réglage clair/sombre du système de chaque administrateur.',
            ])
            ->add('adminColorAccent', ColorType::class, [
                'label' => 'Couleur d\'accent',
                'help' => 'Boutons, menu actif, liens — dans l\'espace d\'administration uniquement.',
            ])
            ->add('adminColorBackground', ColorType::class, [
                'label' => 'Couleur de fond (mode sombre)',
                'help' => 'Utilisée uniquement quand le mode d\'affichage est "Sombre" (ou "Automatique" sur un appareil en mode sombre).',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SiteSetting::class,
        ]);
    }
}
