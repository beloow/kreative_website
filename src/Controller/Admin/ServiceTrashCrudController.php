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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ServiceTrashCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Service::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Service à la corbeille')
            ->setEntityLabelInPlural('Corbeille')
            ->setDefaultSort(['deletedAt' => 'DESC']);
    }

    /**
     * Ne montre que les services qui ont été mis à la corbeille.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.deletedAt IS NOT NULL', $rootAlias));

        return $queryBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        $restore = Action::new('restore', 'Restaurer', 'fa fa-trash-restore')
            ->linkToCrudAction('restoreService')
            ->displayAsLink();

        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $restore)
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action->setLabel('Supprimer définitivement')->setIcon('fa fa-trash'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Titre'),
            TextField::new('icon', 'Icône'),
            TextField::new('priceFrom', 'Tarif indicatif'),
            DateTimeField::new('deletedAt', 'Mis à la corbeille le'),
        ];
    }

    public function restoreService(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager): RedirectResponse
    {
        /** @var Service $service */
        $service = $context->getEntity()->getInstance();
        $service->restore();
        $entityManager->flush();

        $this->addFlash('success', sprintf('"%s" a été restauré. Il est masqué sur le site jusqu\'à ce que tu l\'actives à nouveau.', $service->getTitle()));

        $indexUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($indexUrl);
    }
}
