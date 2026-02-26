<?php

namespace App\Tests\Service;

use App\Service\ModerationService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ModerationServiceTest extends TestCase
{
    private ModerationService $moderationService;

    protected function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->moderationService = new ModerationService($logger);
    }

    /**
     * Test 1: Message normal (doit être approuvé)
     */
    public function testNormalMessageIsApproved(): void
    {
        $content = "Bonjour, comment allez-vous aujourd'hui ?";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertFalse($result['isToxic'], 'Le message normal ne devrait pas être toxique');
        $this->assertFalse($result['isSpam'], 'Le message normal ne devrait pas être spam');
        $this->assertEquals('approved', $result['moderationStatus']);
        $this->assertNull($result['moderationReason']);
    }

    /**
     * Test 2: Message avec insulte (doit être bloqué)
     */
    public function testToxicMessageIsBlocked(): void
    {
        $content = "Tu es un idiot et un con";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isToxic'], 'Le message avec insultes devrait être toxique');
        $this->assertEquals('blocked', $result['moderationStatus']);
        $this->assertEquals('Ce message viole les règles de la communauté', $result['moderationReason']);
        $this->assertGreaterThanOrEqual(0.7, $result['toxicityScore']);
    }

    /**
     * Test 3: Message avec insulte en anglais
     */
    public function testEnglishToxicMessage(): void
    {
        $content = "You are a fucking asshole";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isToxic']);
        $this->assertEquals('blocked', $result['moderationStatus']);
    }

    /**
     * Test 4: Message avec insulte en arabe
     */
    public function testArabicToxicMessage(): void
    {
        $content = "أنت كلب وحمار";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isToxic']);
        $this->assertEquals('blocked', $result['moderationStatus']);
    }

    /**
     * Test 5: Message avec majuscules excessives (CRIER)
     */
    public function testExcessiveCapitalsIsToxic(): void
    {
        $content = "ARRÊTE DE FAIRE ÇA MAINTENANT!!!";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertGreaterThan(0, $result['toxicityScore']);
        $this->assertContains('MAJUSCULES_EXCESSIVES', $result['details']['toxicWords']);
    }

    /**
     * Test 6: Message avec URL (spam)
     */
    public function testMessageWithUrlIsSpam(): void
    {
        $content = "Visitez https://www.spam-site.com pour gagner de l'argent!";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isSpam'], 'Le message avec URL devrait être spam');
        $this->assertEquals('hidden', $result['moderationStatus']);
        $this->assertEquals('Ce message est considéré comme spam', $result['moderationReason']);
    }

    /**
     * Test 7: Message avec www (spam)
     */
    public function testMessageWithWwwIsSpam(): void
    {
        $content = "Allez sur www.publicite.com maintenant";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isSpam']);
        $this->assertEquals('hidden', $result['moderationStatus']);
    }

    /**
     * Test 8: Message avec caractères répétés (spam)
     */
    public function testRepeatedCharactersIsSpam(): void
    {
        $content = "aaaaaaaaaa";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isSpam']);
        $this->assertGreaterThanOrEqual(0.6, $result['spamScore']);
    }

    /**
     * Test 9: Message trop court (spam)
     */
    public function testVeryShortMessageIsSpam(): void
    {
        $content = "ok";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertGreaterThan(0, $result['spamScore']);
        $this->assertContains('MESSAGE_TROP_COURT', $result['details']['spamPatterns']);
    }

    /**
     * Test 10: Message tout en majuscules (spam)
     */
    public function testAllCapsMessageIsSpam(): void
    {
        $content = "ACHETEZ MAINTENANT PROMOTION LIMITÉE";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isSpam']);
        $this->assertContains('TOUT_EN_MAJUSCULES', $result['details']['spamPatterns']);
    }

    /**
     * Test 11: Message avec mots-clés spam
     */
    public function testSpamKeywords(): void
    {
        $content = "Click here to win the lottery prize!";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isSpam']);
        $this->assertEquals('hidden', $result['moderationStatus']);
    }

    /**
     * Test 12: Message avec trop de liens
     */
    public function testTooManyLinksIsSpam(): void
    {
        $content = "Visitez https://site1.com et https://site2.com et https://site3.com";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isSpam']);
        $this->assertContains('TROP_DE_LIENS', $result['details']['spamPatterns']);
    }

    /**
     * Test 13: Message avec plusieurs insultes (score élevé)
     */
    public function testMultipleToxicWords(): void
    {
        $content = "Espèce d'idiot, connard, crétin, débile";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertTrue($result['isToxic']);
        $this->assertGreaterThan(0.9, $result['toxicityScore']);
        $this->assertGreaterThan(2, count($result['details']['toxicWords']));
    }

    /**
     * Test 14: Message limite (juste en dessous du seuil)
     */
    public function testBorderlineMessage(): void
    {
        $content = "C'est vraiment nul ce que tu fais";
        $result = $this->moderationService->analyzeMessage($content);

        // Devrait avoir un score mais pas être bloqué
        $this->assertGreaterThan(0, $result['toxicityScore']);
        $this->assertLessThan(0.7, $result['toxicityScore']);
        $this->assertEquals('approved', $result['moderationStatus']);
    }

    /**
     * Test 15: Vérification du spam utilisateur (messages répétitifs)
     */
    public function testUserSpammingDetection(): void
    {
        $recentMessages = ['Bonjour', 'Bonjour', 'Bonjour'];
        $newMessage = 'Bonjour';

        $isSpamming = $this->moderationService->checkUserSpamming($recentMessages, $newMessage);

        $this->assertTrue($isSpamming, 'Les messages identiques répétés devraient être détectés comme spam');
    }

    /**
     * Test 16: Pas de spam si messages différents
     */
    public function testNoSpammingWithDifferentMessages(): void
    {
        $recentMessages = ['Bonjour', 'Comment ça va?', 'Bien merci'];
        $newMessage = 'Et toi?';

        $isSpamming = $this->moderationService->checkUserSpamming($recentMessages, $newMessage);

        $this->assertFalse($isSpamming);
    }

    /**
     * Test 17: Trop de messages rapides
     */
    public function testTooManyQuickMessages(): void
    {
        $recentMessages = ['msg1', 'msg2', 'msg3', 'msg4', 'msg5', 'msg6'];
        $newMessage = 'msg7';

        $isSpamming = $this->moderationService->checkUserSpamming($recentMessages, $newMessage);

        $this->assertTrue($isSpamming, 'Plus de 5 messages rapides devraient être détectés comme spam');
    }

    /**
     * Test 18: Message de modération pour statut blocked
     */
    public function testGetModerationMessageBlocked(): void
    {
        $message = $this->moderationService->getModerationMessage('blocked');

        $this->assertStringContainsString('viole les règles', $message);
    }

    /**
     * Test 19: Message de modération pour statut hidden
     */
    public function testGetModerationMessageHidden(): void
    {
        $message = $this->moderationService->getModerationMessage('hidden');

        $this->assertStringContainsString('spam', $message);
    }

    /**
     * Test 20: Message de modération avec raison personnalisée
     */
    public function testGetModerationMessageWithCustomReason(): void
    {
        $customReason = 'Raison personnalisée';
        $message = $this->moderationService->getModerationMessage('blocked', $customReason);

        $this->assertEquals($customReason, $message);
    }

    /**
     * Test 21: Score de toxicité limité à 1.0
     */
    public function testToxicityScoreMaximum(): void
    {
        $content = "idiot con connard salaud merde putain imbécile crétin débile stupide";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertLessThanOrEqual(1.0, $result['toxicityScore']);
    }

    /**
     * Test 22: Score de spam limité à 1.0
     */
    public function testSpamScoreMaximum(): void
    {
        $content = "AAAAAAA https://spam.com www.spam.com click here buy now viagra casino";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertLessThanOrEqual(1.0, $result['spamScore']);
    }

    /**
     * Test 23: Message vide
     */
    public function testEmptyMessage(): void
    {
        $content = "";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertEquals('approved', $result['moderationStatus']);
    }

    /**
     * Test 24: Message avec espaces uniquement
     */
    public function testWhitespaceOnlyMessage(): void
    {
        $content = "     ";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertGreaterThan(0, $result['spamScore']);
    }

    /**
     * Test 25: Message avec émojis (normal)
     */
    public function testMessageWithEmojis(): void
    {
        $content = "Bonjour 😊 Comment allez-vous? 👋";
        $result = $this->moderationService->analyzeMessage($content);

        $this->assertEquals('approved', $result['moderationStatus']);
        $this->assertFalse($result['isToxic']);
        $this->assertFalse($result['isSpam']);
    }
}
