<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

    /**
     * La liste principale ne montre jamais les services à la corbeille
     * (ils ont leur propre vue, gérée par ServiceTrashCrudController).
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.deletedAt IS NULL', $rootAlias));

        return $queryBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new('duplicate', 'Dupliquer', 'fa fa-copy')
            ->linkToCrudAction('duplicateService')
            ->displayAsLink();

        return $actions
            ->add(Crud::PAGE_INDEX, $duplicate)
            ->add(Crud::PAGE_DETAIL, $duplicate)
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action->setLabel('Mettre à la corbeille')->setIcon('fa fa-trash'));
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

        // ---- Colonne latérale : panneaux "Publication" et "Catégories", façon WordPress ----
        yield FormField::addColumn(4)->addCssClass('kc-sidebar-column');

        yield FormField::addFieldset('Publication')
            ->setIcon('fa fa-paper-plane')
            ->addCssClass('kc-publish-box');

        yield BooleanField::new('isActive', 'Visible sur le site')
            ->setHelp('Apparaît immédiatement sur la page Services une fois activé.');

        yield IntegerField::new('position', 'Ordre d\'affichage')
            ->setHelp('Les services sont triés du plus petit au plus grand numéro.');

        yield FormField::addFieldset('Catégories')
            ->setIcon('fa fa-tags')
            ->addCssClass('kc-publish-box');

        yield AssociationField::new('categories', 'Catégories')
            ->setFormTypeOption('by_reference', false)
            ->renderAsNativeWidget()
            ->setHelp('Maintiens Ctrl (ou Cmd sur Mac) pour en sélectionner plusieurs. Crée de nouvelles catégories depuis le menu "Catégories".')
            ->formatValue(static function ($value, Service $entity) {
                $names = array_map(static fn ($category) => (string) $category, $entity->getCategories()->toArray());

                return $names === [] ? '—' : implode(', ', $names);
            });
    }

    public function duplicateService(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager): RedirectResponse
    {
        /** @var Service $original */
        $original = $context->getEntity()->getInstance();

        $copy = new Service();
        $copy->setTitle($original->getTitle().' (copie)');
        $copy->setIcon($original->getIcon());
        $copy->setDescription($original->getDescription());
        $copy->setPriceFrom($original->getPriceFrom());
        $copy->setCtaUrl($original->getCtaUrl());
        $copy->setPosition($original->getPosition() + 1);
        $copy->setIsActive(false); // la copie reste masquée tant qu'on ne l'a pas relue/activée

        foreach ($original->getCategories() as $category) {
            $copy->addCategory($category);
        }

        $entityManager->persist($copy);
        $entityManager->flush();

        $this->addFlash('success', sprintf('"%s" a été dupliqué. La copie est masquée par défaut, pense à l\'activer si elle te convient.', $original->getTitle()));

        $editUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_EDIT)
            ->setEntityId($copy->getId())
            ->generateUrl();

        return $this->redirect($editUrl);
    }

    /**
     * Met le service à la corbeille au lieu de le supprimer réellement.
     * La suppression définitive ne se fait que depuis la Corbeille (ServiceTrashCrudController).
     */
    public function deleteEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if ($entityInstance instanceof Service) {
            $entityInstance->trash();
            $entityManager->flush();

            return;
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }
}
