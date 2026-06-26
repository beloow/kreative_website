<?php

namespace App\Form;

use App\Entity\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 100)],
                'attr' => ['placeholder' => 'Jeanne Dupont'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                'attr' => ['placeholder' => 'jeanne@monentreprise.fr'],
            ])
            ->add('company', TextType::class, [
                'label' => 'Entreprise',
                'required' => false,
                'attr' => ['placeholder' => 'Nom de votre entreprise (facultatif)'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 10, max: 2000)],
                'attr' => ['placeholder' => 'Parlez-nous de votre projet...', 'rows' => 5],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
        ]);
    }
}
