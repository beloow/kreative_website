<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ServiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Service::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Service')
            ->setEntityLabelInPlural('Services')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        // ---- Colonne principale : le "contenu" du service, comme l'éditeur d'article WordPress ----
        yield FormField::addColumn(8)->addCssClass('kc-main-column');

        yield TextField::new('title', 'Titre')
            ->addCssClass('kc-field-title')
            ->setFormTypeOption('attr', ['placeholder' => 'Ex : Référencement naturel (SEO)']);

        yield TextareaField::new('description', 'Description')
            ->addCssClass('kc-field-content')
            ->setFormTypeOption('attr', ['rows' => 12, 'placeholder' => "Décrivez ce service en quelques phrases, comme on rédige le corps d'un article..."]);

        yield TextField::new('icon', 'Icône')
            ->setHelp('Nom d\'icône utilisé sur le site : growth, ads, content, seo, social, analytics');

        yield TextField::new('priceFrom', 'Tarif indicatif');

        yield TextField::new('ctaUrl', 'Lien du bouton tarif')
            ->setHelp('URL vers laquelle le bouton "tarif" redirige (ex: /contact, un lien Calendly, un PDF de devis...). Laisse vide pour rediriger vers la page Contact par défaut.');

        // ---- Colonne latérale : panneau "Publication", façon boîte WordPress ----
        yield FormField::addColumn(4)->addCssClass('kc-sidebar-column');

        yield FormField::addFieldset('Publication')
            ->setIcon('fa fa-paper-plane')
            ->addCssClass('kc-publish-box');

        yield BooleanField::new('isActive', 'Visible sur le site')
            ->setHelp('Apparaît immédiatement sur la page Services une fois activé.');

        yield IntegerField::new('position', 'Ordre d\'affichage')
            ->setHelp('Les services sont triés du plus petit au plus grand numéro.');
    }
}
