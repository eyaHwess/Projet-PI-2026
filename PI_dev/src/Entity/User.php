<?php

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`', uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'UNIQ_USER_EMAIL', columns: ['email'])
    ])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    #[Assert\Length(max: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom de famille est obligatoire")]
    #[Assert\Length(max: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "Email invalide")]

    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire")]
    #[Assert\Length(
        min: 8,
        minMessage: "Le mot de passe doit contenir au moins 8 caractères"
    )]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "L’âge doit être positif")]
    private ?int $age = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $speciality = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $specialities = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $availability = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 5)]
    private ?float $rating = null;

    #[ORM\Column(nullable: true)]
    private ?int $reviewCount = 0;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?float $pricePerSession = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoUrl = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $badges = [];

    #[ORM\Column(nullable: true)]
    private ?bool $respondsQuickly = false;

    #[ORM\Column(nullable: true)]
    private ?int $totalSessions = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastActivityAt = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'createdBy')]
    private Collection $posts;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'commenter')]
    private Collection $comments;

    /**
     * @var Collection<int, PostLike>
     */
    #[ORM\OneToMany(targetEntity: PostLike::class, mappedBy: 'Liker')]
    private Collection $postLikes;

    public function __construct()
{
    $this->roles = [UserRole::USER->value];
    $this->status = UserStatus::ACTIVE->value;
    $this->createdAt = new \DateTimeImmutable();
    $this->posts = new ArrayCollection();
    $this->comments = new ArrayCollection();
    $this->postLikes = new ArrayCollection();
}

    public function getId(): ?int
    {
        return $this->id;
    }
   
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function setRole(UserRole $role): self
    {
        $this->roles = [$role->value];
        return $this;
    }

    public function hasRole(UserRole $role): bool
    {
        return in_array($role->value, $this->roles, true);
    }

    public function getStatusEnum(): UserStatus
    {
        return UserStatus::from($this->status);
    }


    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSpeciality(): ?string
    {
        return $this->speciality;
    }

    public function setSpeciality(?string $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSpecialities(): array
    {
        return $this->specialities ?? [];
    }

    /**
     * @param string[] $specialities
     */
    public function setSpecialities(array $specialities): static
    {
        $this->specialities = $specialities;

        return $this;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    public function setAvailability(?string $availability): static
    {
        $this->availability = $availability;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): static
    {
        $this->rating = $rating;

        return $this;
    }
    public function getCreatedAt(): ?\DateTimeImmutable
{
    return $this->createdAt;
}

public function setCreatedAt(\DateTimeImmutable $createdAt): self
{
    $this->createdAt = $createdAt;
    return $this;
}

public function getUpdatedAt(): ?\DateTimeImmutable
{
    return $this->updatedAt;
}

public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
{
    $this->updatedAt = $updatedAt;
    return $this;
}
public function getUserIdentifier(): string
{
    return (string) $this->email;
}

public function eraseCredentials(): void
{
    // Clear temporary sensitive data if needed baad nzidha!!!!!!
}
public function isEnabled(): bool
{
    return $this->status === UserStatus::ACTIVE->value;
}

public function isCoach(): bool
{
    return in_array('ROLE_COACH', $this->roles, true);
}

/**
 * @return Collection<int, Post>
 */
public function getPosts(): Collection
{
    return $this->posts;
}

public function addPost(Post $post): static
{
    if (!$this->posts->contains($post)) {
        $this->posts->add($post);
        $post->setCreatedBy($this);
    }

    return $this;
}

public function removePost(Post $post): static
{
    if ($this->posts->removeElement($post)) {
        // set the owning side to null (unless already changed)
        if ($post->getCreatedBy() === $this) {
            $post->setCreatedBy(null);
        }
    }

    return $this;
}

/**
 * @return Collection<int, Comment>
 */
public function getComments(): Collection
{
    return $this->comments;
}

public function addComment(Comment $comment): static
{
    if (!$this->comments->contains($comment)) {
        $this->comments->add($comment);
        $comment->setCommenter($this);
    }

    return $this;
}

public function removeComment(Comment $comment): static
{
    if ($this->comments->removeElement($comment)) {
        // set the owning side to null (unless already changed)
        if ($comment->getCommenter() === $this) {
            $comment->setCommenter(null);
        }
    }

    return $this;
}

/**
 * @return Collection<int, PostLike>
 */
public function getPostLikes(): Collection
{
    return $this->postLikes;
}

public function addPostLike(PostLike $postLike): static
{
    if (!$this->postLikes->contains($postLike)) {
        $this->postLikes->add($postLike);
        $postLike->setLiker($this);
    }

    return $this;
}

public function removePostLike(PostLike $postLike): static
{
    if ($this->postLikes->removeElement($postLike)) {
        // set the owning side to null (unless already changed)
        if ($postLike->getLiker() === $this) {
            $postLike->setLiker(null);
        }
    }

    return $this;
}

public function getReviewCount(): ?int
{
    return $this->reviewCount;
}

public function setReviewCount(?int $reviewCount): static
{
    $this->reviewCount = $reviewCount;
    return $this;
}

public function getPricePerSession(): ?float
{
    return $this->pricePerSession;
}

public function setPricePerSession(?float $pricePerSession): static
{
    $this->pricePerSession = $pricePerSession;
    return $this;
}

public function getBio(): ?string
{
    return $this->bio;
}

public function setBio(?string $bio): static
{
    $this->bio = $bio;
    return $this;
}

public function getPhotoUrl(): ?string
{
    return $this->photoUrl;
}

public function setPhotoUrl(?string $photoUrl): static
{
    $this->photoUrl = $photoUrl;
    return $this;
}

public function getBadges(): ?array
{
    return $this->badges ?? [];
}

public function setBadges(?array $badges): static
{
    $this->badges = $badges;
    return $this;
}

public function addBadge(string $badge): static
{
    if (!in_array($badge, $this->badges ?? [])) {
        $this->badges[] = $badge;
    }
    return $this;
}

public function getRespondsQuickly(): ?bool
{
    return $this->respondsQuickly;
}

public function setRespondsQuickly(?bool $respondsQuickly): static
{
    $this->respondsQuickly = $respondsQuickly;
    return $this;
}

public function getTotalSessions(): ?int
{
    return $this->totalSessions;
}

public function setTotalSessions(?int $totalSessions): static
{
    $this->totalSessions = $totalSessions;
    return $this;
}

public function getLastActivityAt(): ?\DateTimeImmutable
{
    return $this->lastActivityAt;
}

public function setLastActivityAt(?\DateTimeImmutable $lastActivityAt): static
{
    $this->lastActivityAt = $lastActivityAt;
    return $this;
}

public function updateLastActivity(): static
{
    $this->lastActivityAt = new \DateTimeImmutable();
    return $this;
}

public function isOnline(): bool
{
    if (!$this->lastActivityAt) {
        return false;
    }
    
    $now = new \DateTimeImmutable();
    $diff = $now->getTimestamp() - $this->lastActivityAt->getTimestamp();
    
    // Considéré en ligne si activité dans les 5 dernières minutes
    return $diff < 300;
}

public function getOnlineStatus(): string
{
    if (!$this->lastActivityAt) {
        return 'offline';
    }
    
    $now = new \DateTimeImmutable();
    $diff = $now->getTimestamp() - $this->lastActivityAt->getTimestamp();
    
    if ($diff < 300) { // 5 minutes
        return 'online';
    } elseif ($diff < 3600) { // 1 heure
        return 'away';
    } else {
        return 'offline';
    }
}

}
