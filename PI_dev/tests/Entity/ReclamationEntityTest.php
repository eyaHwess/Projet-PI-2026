<?php

namespace App\Tests\Entity;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Enum\ReclamationStatusEnum;
use App\Enum\ReclamationTypeEnum;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Reclamation (module Reclamation).
 */
class ReclamationEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $reclamation = new Reclamation();
        $this->assertSame(ReclamationStatusEnum::PENDING, $reclamation->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $reclamation->getCreatedAt());
        $this->assertCount(0, $reclamation->getResponses());
    }

    public function testGettersSetters(): void
    {
        $reclamation = new Reclamation();
        $reclamation->setContent('Ma réclamation contient au moins dix caractères.');
        $reclamation->setType(ReclamationTypeEnum::BUG);
        $reclamation->setStatus(ReclamationStatusEnum::IN_PROGRESS);
        $reclamation->setPhotoPath('/uploads/photo.jpg');

        $this->assertSame('Ma réclamation contient au moins dix caractères.', $reclamation->getContent());
        $this->assertSame(ReclamationTypeEnum::BUG, $reclamation->getType());
        $this->assertSame(ReclamationStatusEnum::IN_PROGRESS, $reclamation->getStatus());
        $this->assertSame('/uploads/photo.jpg', $reclamation->getPhotoPath());
    }

    public function testSetUser(): void
    {
        $user = new User();
        $user->setEmail('user@test.com');
        $reclamation = new Reclamation();
        $reclamation->setContent('Contenu de réclamation suffisamment long.');
        $reclamation->setType(ReclamationTypeEnum::OTHER);
        $reclamation->setUser($user);
        $this->assertSame($user, $reclamation->getUser());
    }
}
