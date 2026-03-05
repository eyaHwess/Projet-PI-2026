<?php

namespace App\Tests\Entity;

use App\Entity\CoachingRequest;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité CoachingRequest (module Coaching-Session).
 */
class CoachingRequestEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $request = new CoachingRequest();
        $this->assertSame(CoachingRequest::STATUS_PENDING, $request->getStatus());
        $this->assertSame(CoachingRequest::PRIORITY_NORMAL, $request->getPriority());
        $this->assertInstanceOf(\DateTimeImmutable::class, $request->getCreatedAt());
    }

    public function testGettersSetters(): void
    {
        $request = new CoachingRequest();
        $request->setMessage('Je souhaite un accompagnement pour atteindre mes objectifs.');
        $request->setStatus(CoachingRequest::STATUS_ACCEPTED);
        $request->setPriority(CoachingRequest::PRIORITY_URGENT);
        $request->setGoal('Perte de poids');
        $request->setBudget(500.0);

        $this->assertSame('Je souhaite un accompagnement pour atteindre mes objectifs.', $request->getMessage());
        $this->assertSame(CoachingRequest::STATUS_ACCEPTED, $request->getStatus());
        $this->assertSame(CoachingRequest::PRIORITY_URGENT, $request->getPriority());
        $this->assertSame('Perte de poids', $request->getGoal());
        $this->assertSame(500.0, $request->getBudget());
    }

    public function testSetUserAndCoach(): void
    {
        $user = new User();
        $user->setEmail('user@test.com');
        $coach = new User();
        $coach->setEmail('coach@test.com');

        $request = new CoachingRequest();
        $request->setUser($user);
        $request->setCoach($coach);

        $this->assertSame($user, $request->getUser());
        $this->assertSame($coach, $request->getCoach());
    }
}
