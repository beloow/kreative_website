<?php

namespace App\Form;

use App\Entity\SiteSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class GeneralSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('logoText', TextType::class, [
                'label' => 'Texte du logo',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 100)],
                'help' => 'Affiché dans l\'en-tête et le pied de page du site. Ex : "kreative·studio".',
            ])
            ->add('faviconFile', FileType::class, [
                'label' => 'Favicon (icône d\'onglet)',
                'mapped' => false,
                'required' => false,
                'help' => 'Image carrée (PNG, SVG ou ICO recommandé), idéalement 64×64px ou plus. Laisse vide pour ne pas changer.',
                'constraints' => [
                    new Assert\File(
                        maxSize: '1024k',
                        mimeTypes: ['image/png', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'],
                        mimeTypesMessage: 'Merci de déposer une image PNG, SVG ou ICO.',
                    ),
                ],
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
