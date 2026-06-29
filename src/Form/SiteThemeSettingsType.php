<?php

namespace App\Form;

use App\Entity\SiteSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SiteThemeSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('siteColorAccent', ColorType::class, [
                'label' => 'Couleur d\'accent',
                'help' => 'Boutons, liens, éléments mis en avant sur le site public.',
            ])
            ->add('siteColorBackground', ColorType::class, [
                'label' => 'Couleur de fond',
                'help' => 'Fond principal du site public.',
            ])
            ->add('siteFontHeading', TextType::class, [
                'label' => 'Police des titres',
                'constraints' => [new Assert\NotBlank()],
                'help' => 'Nom exact d\'une police disponible sur <a href="https://fonts.google.com" target="_blank" rel="noopener">Google Fonts</a>, ex : "Fraunces", "Playfair Display", "Poppins"...',
            ])
            ->add('siteFontBody', TextType::class, [
                'label' => 'Police du texte',
                'constraints' => [new Assert\NotBlank()],
                'help' => 'Idem, ex : "Inter", "Roboto", "Lato"...',
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
