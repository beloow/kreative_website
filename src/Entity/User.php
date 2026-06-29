<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_COLLABORATOR = 'ROLE_COLLABORATOR';

    public const ROLE_CHOICES = [
        'Administrateur' => self::ROLE_ADMIN,
        'Collaborateur' => self::ROLE_COLLABORATOR,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 30)]
    private string $role = self::ROLE_COLLABORATOR;

    #[ORM\Column]
    private ?string $password = null;

    /**
     * Champ purement transitoire (non persisté) utilisé uniquement par le formulaire admin
     * pour saisir un mot de passe en clair, qui sera haché avant d'être stocké dans $password.
     */
    private ?string $plainPassword = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function isAdmin(): bool
    {
        return self::ROLE_ADMIN === $this->role;
    }

    public function getRoles(): array
    {
        return array_unique([$this->role, 'ROLE_USER']);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
