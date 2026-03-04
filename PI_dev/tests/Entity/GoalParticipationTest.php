<?php

namespace App\Tests\Entity;

use App\Entity\GoalParticipation;
use App\Entity\User;
use App\Entity\Goal;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité GoalParticipation
 */
class GoalParticipationTest extends TestCase
{
    /**
     * Test 1 : Vérifier que la participation peut être créée
     */
    public function testJoinGoal(): void
    {
        $participation = new GoalParticipation();

        $this->assertNotNull($participation);
        $this->assertInstanceOf(GoalParticipation::class, $participation);
    }

    /**
     * Test 2 : Vérifier que la participation a un utilisateur
     */
    public function testParticipationUser(): void
    {
        $participation = new GoalParticipation();
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $participation->setUser($user);

        $this->assertInstanceOf(User::class, $participation->getUser());
        $this->assertEquals('John', $participation->getUser()->getFirstName());
    }

    /**
     * Test 3 : Vérifier que la participation a un goal
     */
    public function testParticipationGoal(): void
    {
        $participation = new GoalParticipation();
        $goal = new Goal();
        $goal->setTitle('Objectif de test');

        $participation->setGoal($goal);

        $this->assertInstanceOf(Goal::class, $participation->getGoal());
        $this->assertEquals('Objectif de test', $participation->getGoal()->getTitle());
    }

    /**
     * Test 4 : Vérifier que la participation a un rôle
     */
    public function testParticipationRole(): void
    {
        $participation = new GoalParticipation();
        $participation->setRole('MEMBER');

        $this->assertEquals('MEMBER', $participation->getRole());
    }

    /**
     * Test 5 : Vérifier les différents rôles possibles
     */
    public function testParticipationRoles(): void
    {
        $participation = new GoalParticipation();

        // Test OWNER
        $participation->setRole('OWNER');
        $this->assertEquals('OWNER', $participation->getRole());

        // Test ADMIN
        $participation->setRole('ADMIN');
        $this->assertEquals('ADMIN', $participation->getRole());

        // Test MEMBER
        $participation->setRole('MEMBER');
        $this->assertEquals('MEMBER', $participation->getRole());
    }

    /**
     * Test 6 : Vérifier que la participation peut être approuvée
     */
    public function testParticipationApproved(): void
    {
        $participation = new GoalParticipation();
        $participation->setStatus('APPROVED');

        $this->assertEquals('APPROVED', $participation->getStatus());
    }

    /**
     * Test 7 : Vérifier que la participation peut être en attente
     */
    public function testParticipationPending(): void
    {
        $participation = new GoalParticipation();
        $participation->setStatus('PENDING');

        $this->assertEquals('PENDING', $participation->getStatus());
    }

    /**
     * Test 8 : Vérifier que la participation peut être rejetée
     */
    public function testParticipationRejected(): void
    {
        $participation = new GoalParticipation();
        $participation->setStatus('REJECTED');

        $this->assertEquals('REJECTED', $participation->getStatus());
    }

    /**
     * Test 9 : Vérifier la date de création
     */
    public function testParticipationCreatedAt(): void
    {
        $participation = new GoalParticipation();
        $now = new \DateTime();
        $participation->setCreatedAt($now);

        $this->assertEquals($now, $participation->getCreatedAt());
    }

    /**
     * Test 10 : Vérifier la méthode isApproved()
     */
    public function testIsApproved(): void
    {
        $participation = new GoalParticipation();
        
        // Test avec status APPROVED
        $participation->setStatus('APPROVED');
        $this->assertTrue($participation->isApproved());
        
        // Test avec status PENDING
        $participation->setStatus('PENDING');
        $this->assertFalse($participation->isApproved());
    }

    /**
     * Test 11 : Vérifier la méthode canModerate() pour OWNER
     */
    public function testOwnerCanModerate(): void
    {
        $participation = new GoalParticipation();
        $participation->setRole('OWNER');

        $this->assertTrue($participation->canModerate());
    }

    /**
     * Test 12 : Vérifier la méthode canModerate() pour ADMIN
     */
    public function testAdminCanModerate(): void
    {
        $participation = new GoalParticipation();
        $participation->setRole('ADMIN');

        $this->assertTrue($participation->canModerate());
    }

    /**
     * Test 13 : Vérifier la méthode canModerate() pour MEMBER
     */
    public function testMemberCannotModerate(): void
    {
        $participation = new GoalParticipation();
        $participation->setRole('MEMBER');

        $this->assertFalse($participation->canModerate());
    }

    /**
     * Test 14 : Règle métier - Seuls les membres approuvés peuvent accéder au chatroom
     */
    public function testOnlyApprovedMembersCanAccessChatroom(): void
    {
        $participation = new GoalParticipation();
        
        // Membre approuvé
        $participation->setStatus('APPROVED');
        $this->assertTrue($participation->isApproved());
        
        // Membre en attente
        $participation->setStatus('PENDING');
        $this->assertFalse($participation->isApproved());
        
        // Membre rejeté
        $participation->setStatus('REJECTED');
        $this->assertFalse($participation->isApproved());
    }

    /**
     * Test 15 : Règle métier - OWNER et ADMIN peuvent modérer
     */
    public function testModerationPermissions(): void
    {
        $ownerParticipation = new GoalParticipation();
        $ownerParticipation->setRole('OWNER');
        
        $adminParticipation = new GoalParticipation();
        $adminParticipation->setRole('ADMIN');
        
        $memberParticipation = new GoalParticipation();
        $memberParticipation->setRole('MEMBER');

        $this->assertTrue($ownerParticipation->canModerate());
        $this->assertTrue($adminParticipation->canModerate());
        $this->assertFalse($memberParticipation->canModerate());
    }
}
