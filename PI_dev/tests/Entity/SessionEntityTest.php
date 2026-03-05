<?php

namespace App\Tests\Entity;

use App\Entity\Session;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Session (module Coaching-Session).
 */
class SessionEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $session = new Session();
        $this->assertSame(Session::STATUS_SCHEDULING, $session->getStatus());
        $this->assertSame(Session::PAYMENT_STATUS_PENDING, $session->getPaymentStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $session->getCreatedAt());
    }

    public function testGettersSetters(): void
    {
        $session = new Session();
        $session->setStatus(Session::STATUS_CONFIRMED);
        $session->setPriority(Session::PRIORITY_HIGH);
        $session->setDuration(60);
        $session->setObjective('Objectif test');
        $session->setPrice(50.0);
        $session->setPaymentStatus(Session::PAYMENT_STATUS_PAID);

        $this->assertSame(Session::STATUS_CONFIRMED, $session->getStatus());
        $this->assertSame(Session::PRIORITY_HIGH, $session->getPriority());
        $this->assertSame(60, $session->getDuration());
        $this->assertSame('Objectif test', $session->getObjective());
        $this->assertSame(50.0, $session->getPrice());
        $this->assertSame(Session::PAYMENT_STATUS_PAID, $session->getPaymentStatus());
    }

    public function testScheduledAt(): void
    {
        $session = new Session();
        $date = new \DateTimeImmutable('+1 week');
        $session->setScheduledAt($date);
        $this->assertSame($date, $session->getScheduledAt());
    }
}
