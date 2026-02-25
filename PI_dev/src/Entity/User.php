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
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'UNIQ_USER_EMAIL', columns: ['email'])
])]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $speciality = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $availability = null;

    #[ORM\Column(nullable: true)]
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

    // VichUploader field for profile picture
    #[Vich\UploadableField(mapping: 'user_profiles', fileNameProperty: 'profilePictureName', size: 'profilePictureSize')]
    private ?File $profilePictureFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $profilePictureName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $profilePictureSize = null;

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

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'commenter')]
    private Collection $comments;

    #[ORM\OneToMany(targetEntity: PostLike::class, mappedBy: 'liker')]
    private Collection $postLikes;

    /**
     * @var Collection<int, GoalParticipation>
     */
    #[ORM\OneToMany(targetEntity: GoalParticipation::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $goalParticipations;

    public function __construct()
    {
        $this->roles = [UserRole::USER->value];
        $this->status = UserStatus::ACTIVE->value;
        $this->createdAt = new \DateTimeImmutable();

        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->postLikes = new ArrayCollection();
        $this->goalParticipations = new ArrayCollection();
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

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): static { $this->lastName = $lastName; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getRoles(): array { return array_unique($this->roles); }

    public function setRole(UserRole $role): self
    {
        $this->roles = [$role->value];
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials(): void {}

    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function setPhoneNumber(?string $phoneNumber): static { $this->phoneNumber = $phoneNumber; return $this; }

    

    

    public function getPosts(): Collection { return $this->posts; }
    public function addPost(Post $post): static {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setCreatedBy($this);
        }
        return $this;
    }

   

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

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

public function isCoach(): bool
{
    return in_array('ROLE_COACH', $this->roles, true);
}

/**
 * @return Collection<int, GoalParticipation>
 */
public function getGoalParticipations(): Collection
{
    return $this->goalParticipations;
}

public function addGoalParticipation(GoalParticipation $goalParticipation): static
{
    if (!$this->goalParticipations->contains($goalParticipation)) {
        $this->goalParticipations->add($goalParticipation);
        $goalParticipation->setUser($this);
    }

    return $this;
}

public function removeGoalParticipation(GoalParticipation $goalParticipation): static
{
    if ($this->goalParticipations->removeElement($goalParticipation)) {
        if ($goalParticipation->getUser() === $this) {
            $goalParticipation->setUser(null);
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

// VichUploader methods for profile picture
public function setProfilePictureFile(?File $profilePictureFile = null): void
{
    $this->profilePictureFile = $profilePictureFile;

    if (null !== $profilePictureFile) {
        // Update updatedAt to trigger VichUploader
        $this->updatedAt = new \DateTimeImmutable();
    }
}

public function getProfilePictureFile(): ?File
{
    return $this->profilePictureFile;
}

public function setProfilePictureName(?string $profilePictureName): void
{
    $this->profilePictureName = $profilePictureName;
}

public function getProfilePictureName(): ?string
{
    return $this->profilePictureName;
}

public function setProfilePictureSize(?int $profilePictureSize): void
{
    $this->profilePictureSize = $profilePictureSize;
}

public function getProfilePictureSize(): ?int
{
    return $this->profilePictureSize;
}

public function getFormattedProfilePictureSize(): string
{
    if (!$this->profilePictureSize) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $size = $this->profilePictureSize;
    $unitIndex = 0;

    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }

    return round($size, 2) . ' ' . $units[$unitIndex];
}

public function hasProfilePicture(): bool
{
    return $this->profilePictureName !== null;
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
