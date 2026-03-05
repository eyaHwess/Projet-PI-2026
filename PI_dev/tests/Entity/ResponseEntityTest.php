<?php

namespace App\Tests\Entity;

use App\Entity\Reclamation;
use App\Entity\Response;
use App\Entity\User;
use App\Enum\ReclamationTypeEnum;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Response (module Reclamation — réponses aux réclamations).
 */
class ResponseEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $response = new Response();
        $this->assertInstanceOf(\DateTimeImmutable::class, $response->getCreatedAt());
    }

    public function testGettersSetters(): void
    {
        $response = new Response();
        $response->setContent('Réponse officielle à la réclamation.');
        $date = new \DateTimeImmutable();
        $response->setCreatedAt($date);

        $this->assertSame('Réponse officielle à la réclamation.', $response->getContent());
        $this->assertSame($date, $response->getCreatedAt());
    }

    public function testSetReclamation(): void
    {
        $user = new User();
        $user->setEmail('user@test.com');
        $reclamation = new Reclamation();
        $reclamation->setContent('Réclamation avec assez de caractères.');
        $reclamation->setType(ReclamationTypeEnum::OTHER);
        $reclamation->setUser($user);

        $response = new Response();
        $response->setContent('Contenu de la réponse.');
        $response->setReclamation($reclamation);

        $this->assertSame($reclamation, $response->getReclamation());
    }
}
