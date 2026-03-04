<?php

namespace App\Tests\Service;

use App\Entity\CoachingRequest;
use App\Entity\User;
use App\Service\CoachingRequestManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CoachingRequestManagerTest extends TestCase
{
    private CoachingRequestManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new CoachingRequestManager();
    }

    /**
     * Test 1 : Demande valide → validate() retourne true.
     */
    public function testValidRequest(): void
    {
        $request = $this->createValidCoachingRequest();

        $result = $this->manager->validate($request);

        $this->assertTrue($result);
    }

    /**
     * Test 2 : Demande sans message → InvalidArgumentException.
     */
    public function testRequestWithoutMessage(): void
    {
        $request = $this->createValidCoachingRequest();
        $request->setMessage('');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le message de la demande de coaching est obligatoire et ne doit pas être vide.');

        $this->manager->validate($request);
    }

    /**
     * Test 3 : Demande avec statut invalide (non "pending") → InvalidArgumentException.
     */
    public function testInvalidStatus(): void
    {
        $request = $this->createValidCoachingRequest();
        $request->setStatus(CoachingRequest::STATUS_ACCEPTED);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut initial d\'une demande doit être "pending"');

        $this->manager->validate($request);
    }

    /**
     * Test complémentaire : Demande sans coach → InvalidArgumentException.
     */
    public function testRequestWithoutCoach(): void
    {
        $request = $this->createValidCoachingRequest();
        $request->setCoach(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Un coach doit être sélectionné pour la demande de coaching.');

        $this->manager->validate($request);
    }

    /**
     * Test complémentaire : Message avec espaces uniquement → InvalidArgumentException.
     */
    public function testRequestWithWhitespaceOnlyMessage(): void
    {
        $request = $this->createValidCoachingRequest();
        $request->setMessage('   ');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le message de la demande de coaching est obligatoire et ne doit pas être vide.');

        $this->manager->validate($request);
    }

    private function createValidCoachingRequest(): CoachingRequest
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setPassword('hashedpassword');

        $coach = new User();
        $coach->setEmail('coach@example.com');
        $coach->setFirstName('Marie');
        $coach->setLastName('Martin');
        $coach->setPassword('hashedpassword');

        $request = new CoachingRequest();
        $request->setUser($user);
        $request->setCoach($coach);
        $request->setMessage('Je souhaite un accompagnement pour atteindre mes objectifs sportifs.');

        return $request;
    }
}
