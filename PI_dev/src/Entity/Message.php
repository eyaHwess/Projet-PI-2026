<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[Vich\Uploadable]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPinned = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isEdited = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $editedAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $attachmentPath = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $attachmentType = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $attachmentOriginalName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $audioDuration = null;

    // VichUploader fields
    #[Vich\UploadableField(mapping: 'message_images', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $imageSize = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chatroom $chatroom = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Message $replyTo = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'replyTo')]
    private Collection $replies;

    #[ORM\OneToMany(targetEntity: MessageReaction::class, mappedBy: 'message', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $reactions;

    public function __construct()
    {
        $this->reactions = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): static { $this->content = $content; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getChatroom(): ?Chatroom { return $this->chatroom; }
    public function setChatroom(?Chatroom $chatroom): static { $this->chatroom = $chatroom; return $this; }

    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $author): static { $this->author = $author; return $this; }

    public function getIsPinned(): bool { return $this->isPinned; }
    public function setIsPinned(bool $isPinned): static { $this->isPinned = $isPinned; return $this; }

    public function getIsEdited(): bool { return $this->isEdited; }
    public function setIsEdited(bool $isEdited): static { $this->isEdited = $isEdited; return $this; }

    public function getEditedAt(): ?\DateTimeInterface { return $this->editedAt; }
    public function setEditedAt(?\DateTimeInterface $editedAt): static { $this->editedAt = $editedAt; return $this; }

    public function getAttachmentPath(): ?string { return $this->attachmentPath; }
    public function setAttachmentPath(?string $attachmentPath): static { $this->attachmentPath = $attachmentPath; return $this; }

    public function getAttachmentType(): ?string { return $this->attachmentType; }
    public function setAttachmentType(?string $attachmentType): static { $this->attachmentType = $attachmentType; return $this; }

    public function getAttachmentOriginalName(): ?string { return $this->attachmentOriginalName; }
    public function setAttachmentOriginalName(?string $attachmentOriginalName): static { $this->attachmentOriginalName = $attachmentOriginalName; return $this; }

    public function getAudioDuration(): ?int { return $this->audioDuration; }
    public function setAudioDuration(?int $audioDuration): static { $this->audioDuration = $audioDuration; return $this; }

    /**
     * Check if message is a voice message
     */
    public function isVoiceMessage(): bool
    {
        return $this->attachmentType === 'audio';
    }

    /**
     * Format audio duration as MM:SS
     */
    public function getFormattedDuration(): string
    {
        if (!$this->audioDuration) {
            return '0:00';
        }
        $minutes = floor($this->audioDuration / 60);
        $seconds = $this->audioDuration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get attachment icon based on type
     */
    public function getAttachmentIcon(): string
    {
        return match($this->attachmentType) {
            'image' => 'fa-image',
            'pdf' => 'fa-file-pdf',
            'document' => 'fa-file-word',
            'excel' => 'fa-file-excel',
            'text' => 'fa-file-alt',
            default => 'fa-file',
        };
    }

    /**
     * Check if message has attachment
     */
    public function hasAttachment(): bool
    {
        return $this->attachmentPath !== null;
    }

    /**
     * @return Collection<int, MessageReaction>
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(MessageReaction $reaction): static
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions->add($reaction);
            $reaction->setMessage($this);
        }
        return $this;
    }

    public function removeReaction(MessageReaction $reaction): static
    {
        if ($this->reactions->removeElement($reaction)) {
            if ($reaction->getMessage() === $this) {
                $reaction->setMessage(null);
            }
        }
        return $this;
    }

    /**
     * Get count of reactions by type
     */
    public function getReactionCount(string $type): int
    {
        return $this->reactions->filter(function(MessageReaction $reaction) use ($type) {
            return $reaction->getReactionType() === $type;
        })->count();
    }

    /**
     * Check if user has reacted with specific type
     */
    public function hasUserReacted(User $user, string $type): bool
    {
        return $this->reactions->exists(function($key, MessageReaction $reaction) use ($user, $type) {
            return $reaction->getUser()->getId() === $user->getId() 
                && $reaction->getReactionType() === $type;
        });
    }

    /**
     * Check if message has been read by at least one user (excluding author)
     */
    public function isRead(): bool
    {
        // This will be checked via repository in controller
        return false;
    }

    /**
     * Get total participants count in chatroom (excluding author)
     */
    public function getTotalParticipants(): int
    {
        return $this->chatroom->getGoal()->getGoalParticipations()->count() - 1;
    }

    public function getReplyTo(): ?self
    {
        return $this->replyTo;
    }

    public function setReplyTo(?self $replyTo): static
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(self $reply): static
    {
        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
            $reply->setReplyTo($this);
        }
        return $this;
    }

    public function removeReply(self $reply): static
    {
        if ($this->replies->removeElement($reply)) {
            if ($reply->getReplyTo() === $this) {
                $reply->setReplyTo(null);
            }
        }
        return $this;
    }

    /**
     * Check if this message is a reply
     */
    public function isReply(): bool
    {
        return $this->replyTo !== null;
    }

    // VichUploader methods
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedFileSize(): string
    {
        if (!$this->imageSize) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->imageSize;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
