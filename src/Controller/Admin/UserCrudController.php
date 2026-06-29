<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(fn (User $user) => $this->canDelete($user));
            });
    }

    public function configureFields(string $pageName): iterable
    {
        $isNew = Crud::PAGE_NEW === $pageName;

        return [
            EmailField::new('email', 'E-mail'),
            ChoiceField::new('role', 'Rôle')
                ->setChoices(User::ROLE_CHOICES)
                ->setHelp('Administrateur : accès complet, y compris les utilisateurs et les réglages. Collaborateur : accès au tableau de bord et aux statistiques en lecture seule, et peut gérer les Services et les Demandes de contact.'),
            TextField::new('plainPassword', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setRequired($isNew)
                ->onlyOnForms()
                ->setHelp($isNew ? 'Au moins 8 caractères.' : 'Laisse ce champ vide pour ne pas changer le mot de passe actuel.'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->hashPasswordIfNeeded($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->hashPasswordIfNeeded($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function delete(AdminContext $context): Response
    {
        $entityInstance = $context->getEntity()->getInstance();

        if ($entityInstance instanceof User && !$this->canDelete($entityInstance)) {
            $this->addFlash('danger', $this->getDeleteRefusalReason($entityInstance));

            $indexUrl = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Crud::PAGE_INDEX)
                ->generateUrl();

            return $this->redirect($indexUrl);
        }

        return parent::delete($context);
    }

    private function hashPasswordIfNeeded(User $user): void
    {
        if ($user->getPlainPassword()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()));
            $user->setPlainPassword(null);
        }
    }

    private function canDelete(User $user): bool
    {
        if ($user === $this->getUser()) {
            return false;
        }

        if ($user->isAdmin() && $this->userRepository->count(['role' => User::ROLE_ADMIN]) <= 1) {
            return false;
        }

        return true;
    }

    private function getDeleteRefusalReason(User $user): string
    {
        if ($user === $this->getUser()) {
            return 'Tu ne peux pas supprimer ton propre compte.';
        }

        return 'Impossible de supprimer le dernier compte administrateur restant.';
    }
}
