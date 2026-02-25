<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $translatedContent = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $translatedLanguage = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $attachmentPath = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $attachmentType = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $attachmentOriginalName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $audioDuration = null;

    // VichUploader fields for images
    #[Vich\UploadableField(mapping: 'message_images', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $imageSize = null;

    // VichUploader for general files (documents, audio, video, etc.)
    #[Vich\UploadableField(mapping: 'message_files', fileNameProperty: 'fileName', size: 'fileSize')]
    private ?File $file = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $fileSize = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $fileType = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    // Moderation fields
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isToxic = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isSpam = false;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'approved'])]
    private string $moderationStatus = 'approved'; // approved, blocked, hidden, pending

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $toxicityScore = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $spamScore = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $moderationReason = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Chatroom $chatroom = null;

    #[ORM\ManyToOne(inversedBy: 'messages', targetEntity: PrivateChatroom::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PrivateChatroom $privateChatroom = null;

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

    public function getPrivateChatroom(): ?PrivateChatroom { return $this->privateChatroom; }
    public function setPrivateChatroom(?PrivateChatroom $privateChatroom): static { $this->privateChatroom = $privateChatroom; return $this; }

    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $author): static { $this->author = $author; return $this; }

    public function getIsPinned(): bool { return $this->isPinned; }
    public function setIsPinned(bool $isPinned): static { $this->isPinned = $isPinned; return $this; }

    public function getIsEdited(): bool { return $this->isEdited; }
    public function setIsEdited(bool $isEdited): static { $this->isEdited = $isEdited; return $this; }

    public function getEditedAt(): ?\DateTimeInterface { return $this->editedAt; }
    public function setEditedAt(?\DateTimeInterface $editedAt): static { $this->editedAt = $editedAt; return $this; }

    public function getTranslatedContent(): ?string { return $this->translatedContent; }
    public function setTranslatedContent(?string $translatedContent): static { $this->translatedContent = $translatedContent; return $this; }

    public function getTranslatedLanguage(): ?string { return $this->translatedLanguage; }
    public function setTranslatedLanguage(?string $translatedLanguage): static
    {
        $this->translatedLanguage = $translatedLanguage ? strtolower($translatedLanguage) : null;
        return $this;
    }

    public function getAttachmentPath(): ?string { return $this->attachmentPath; }
    public function setAttachmentPath(?string $attachmentPath): static { $this->attachmentPath = $attachmentPath; return $this; }

    public function getAttachmentType(): ?string { return $this->attachmentType; }
    public function setAttachmentType(?string $attachmentType): static { $this->attachmentType = $attachmentType; return $this; }

    public function getAttachmentOriginalName(): ?string { return $this->attachmentOriginalName; }
    public function setAttachmentOriginalName(?string $attachmentOriginalName): static { $this->attachmentOriginalName = $attachmentOriginalName; return $this; }

    public function getAudioDuration(): ?int { return $this->audioDuration; }
    public function setAudioDuration(?int $audioDuration): static { $this->audioDuration = $audioDuration; return $this; }

    public function isVoiceMessage(): bool
    {
        return $this->attachmentType === 'audio';
    }

    public function getFormattedDuration(): string
    {
        if (!$this->audioDuration) {
            return '0:00';
        }
        $minutes = floor($this->audioDuration / 60);
        $seconds = $this->audioDuration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

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

    public function hasAttachment(): bool
    {
        return $this->attachmentPath !== null || $this->imageName !== null || $this->fileName !== null;
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

    public function getReactionCount(string $type): int
    {
        return $this->reactions->filter(function(MessageReaction $reaction) use ($type) {
            return $reaction->getReactionType() === $type;
        })->count();
    }

    public function hasUserReacted(User $user, string $type): bool
    {
        return $this->reactions->exists(function($key, MessageReaction $reaction) use ($user, $type) {
            return $reaction->getUser()->getId() === $user->getId() 
                && $reaction->getReactionType() === $type;
        });
    }

    public function isRead(): bool
    {
        return false;
    }

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

    public function isReply(): bool
    {
        return $this->replyTo !== null;
    }

    // VichUploader methods for images
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTime();
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

    // VichUploader methods for general files
    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            $this->updatedAt = new \DateTime();
            $this->fileType = $file->getMimeType();
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileSize(?int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileType(?string $fileType): void
    {
        $this->fileType = $fileType;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function getFormattedGeneralFileSize(): string
    {
        if (!$this->fileSize) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->fileSize;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function hasFile(): bool
    {
        return $this->fileName !== null;
    }

    public function getFileIcon(): string
    {
        if (!$this->fileType) {
            return 'fa-file';
        }

        return match(true) {
            str_starts_with($this->fileType, 'image/') => 'fa-image',
            str_starts_with($this->fileType, 'video/') => 'fa-video',
            str_starts_with($this->fileType, 'audio/') => 'fa-music',
            $this->fileType === 'application/pdf' => 'fa-file-pdf',
            str_contains($this->fileType, 'word') || str_contains($this->fileType, 'document') => 'fa-file-word',
            str_contains($this->fileType, 'excel') || str_contains($this->fileType, 'spreadsheet') => 'fa-file-excel',
            str_contains($this->fileType, 'powerpoint') || str_contains($this->fileType, 'presentation') => 'fa-file-powerpoint',
            str_starts_with($this->fileType, 'text/') => 'fa-file-alt',
            default => 'fa-file',
        };
    }

    // Moderation getters and setters
    public function getIsToxic(): bool
    {
        return $this->isToxic;
    }

    public function setIsToxic(bool $isToxic): static
    {
        $this->isToxic = $isToxic;
        return $this;
    }

    public function getIsSpam(): bool
    {
        return $this->isSpam;
    }

    public function setIsSpam(bool $isSpam): static
    {
        $this->isSpam = $isSpam;
        return $this;
    }

    public function getModerationStatus(): string
    {
        return $this->moderationStatus;
    }

    public function setModerationStatus(string $moderationStatus): static
    {
        $this->moderationStatus = $moderationStatus;
        return $this;
    }

    public function getToxicityScore(): ?float
    {
        return $this->toxicityScore;
    }

    public function setToxicityScore(?float $toxicityScore): static
    {
        $this->toxicityScore = $toxicityScore;
        return $this;
    }

    public function getSpamScore(): ?float
    {
        return $this->spamScore;
    }

    public function setSpamScore(?float $spamScore): static
    {
        $this->spamScore = $spamScore;
        return $this;
    }

    public function getModerationReason(): ?string
    {
        return $this->moderationReason;
    }

    public function setModerationReason(?string $moderationReason): static
    {
        $this->moderationReason = $moderationReason;
        return $this;
    }

    public function isModerated(): bool
    {
        return $this->moderationStatus !== 'approved';
    }

    public function isBlocked(): bool
    {
        return $this->moderationStatus === 'blocked';
    }

    public function isHidden(): bool
    {
        return $this->moderationStatus === 'hidden';
    }
}
