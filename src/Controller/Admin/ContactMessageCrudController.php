<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContactMessageCrudController extends AbstractCrudController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ContactMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Demande de contact')
            ->setEntityLabelInPlural('Demandes de contact')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $toggleStatus = Action::new('toggleStatus', '', 'fa fa-exchange-alt')
            ->linkToCrudAction('toggleStatus')
            ->displayAsLink()
            ->setCssClass('action-toggle-status')
            ->setHtmlAttributes(['title' => 'Basculer le statut'])
            ->setLabel(static function (?ContactMessage $message): string {
                return $message?->isHandled() ? 'Remettre en attente' : 'Marquer comme traité';
            });

        $markHandled = Action::new('markHandled', 'Marquer comme traité', 'fa fa-check')
            ->createAsBatchAction()
            ->linkToCrudAction('batchMarkHandled');

        $markPending = Action::new('markPending', 'Remettre en attente', 'fa fa-undo')
            ->createAsBatchAction()
            ->linkToCrudAction('batchMarkPending');

        // Les demandes de contact ne se créent que via le formulaire public
        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, $toggleStatus)
            ->add(Crud::PAGE_DETAIL, $toggleStatus)
            ->addBatchAction($markHandled)
            ->addBatchAction($markPending);
    }

    public function batchMarkHandled(BatchActionDto $batchActionDto, ContactMessageRepository $repository, EntityManagerInterface $entityManager): RedirectResponse
    {
        return $this->applyBatchStatus($batchActionDto, $repository, $entityManager, true);
    }

    public function batchMarkPending(BatchActionDto $batchActionDto, ContactMessageRepository $repository, EntityManagerInterface $entityManager): RedirectResponse
    {
        return $this->applyBatchStatus($batchActionDto, $repository, $entityManager, false);
    }

    private function applyBatchStatus(BatchActionDto $batchActionDto, ContactMessageRepository $repository, EntityManagerInterface $entityManager, bool $isHandled): RedirectResponse
    {
        $messages = $repository->findBy(['id' => $batchActionDto->getEntityIds()]);

        foreach ($messages as $message) {
            $message->setIsHandled($isHandled);
        }

        $entityManager->flush();

        $this->addFlash('success', sprintf(
            '%d demande(s) %s.',
            count($messages),
            $isHandled ? 'marquée(s) comme traitée(s)' : 'remise(s) en attente'
        ));

        $indexUrl = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($indexUrl);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('fullName', 'Nom')->setTemplatePath('admin/fields/contact_name.html.twig'),
            EmailField::new('email', 'E-mail'),
            TextField::new('company', 'Entreprise'),
            TextareaField::new('message', 'Message'),
            DateTimeField::new('createdAt', 'Reçu le')->setFormTypeOption('disabled', true),
            BooleanField::new('isHandled', 'Statut')->setTemplatePath('admin/fields/contact_status.html.twig'),
        ];
    }

    public function toggleStatus(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager): RedirectResponse
    {
        /** @var ContactMessage $message */
        $message = $context->getEntity()->getInstance();
        $message->setIsHandled(!$message->isHandled());
        $entityManager->flush();

        $this->addFlash('success', $message->isHandled()
            ? sprintf('Demande de "%s" marquée comme traitée.', $message->getFullName())
            : sprintf('Demande de "%s" remise en attente.', $message->getFullName()));

        $indexUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($indexUrl);
    }
}
