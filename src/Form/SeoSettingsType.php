<?php

namespace App\Form;

use App\Entity\SiteSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SeoSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->pages() as $key => $label) {
            $builder
                ->add('seo'.$key.'Title', TextType::class, [
                    'label' => 'Titre — '.$label,
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 70)],
                    'help' => 'Affiché dans l\'onglet du navigateur et les résultats Google. 70 caractères max conseillés.',
                ])
                ->add('seo'.$key.'Description', TextareaType::class, [
                    'label' => 'Description — '.$label,
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 160)],
                    'attr' => ['rows' => 2],
                    'help' => 'Le résumé affiché sous le titre dans les résultats de recherche. 160 caractères max conseillés.',
                ])
            ;
        }

        $builder->add('ogImageFile', FileType::class, [
            'label' => 'Image de partage (réseaux sociaux)',
            'mapped' => false,
            'required' => false,
            'help' => 'Affichée quand le site est partagé sur Facebook, LinkedIn, X... Format recommandé : 1200×630px. Laisse vide pour garder l\'image actuelle.',
            'constraints' => [
                new Assert\File(
                    maxSize: '2048k',
                    mimeTypes: ['image/png', 'image/jpeg', 'image/webp'],
                    mimeTypesMessage: 'Merci de déposer une image PNG, JPEG ou WebP.',
                ),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SiteSetting::class,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function pages(): array
    {
        return [
            'Home' => 'Accueil',
            'About' => 'À propos',
            'Services' => 'Services',
            'Contact' => 'Contact',
        ];
    }
}
