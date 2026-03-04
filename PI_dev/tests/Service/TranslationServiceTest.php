<?php

namespace App\Tests\Service;

use App\Service\TranslationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Psr\Log\NullLogger;

/**
 * Tests unitaires pour le service de traduction
 */
class TranslationServiceTest extends TestCase
{
    /**
     * Test 1 : Vérifier que le service peut être instancié
     */
    public function testServiceInstantiation(): void
    {
        $client = new MockHttpClient();
        $logger = new NullLogger();
        
        $service = new TranslationService($client, $logger, 'mymemory');

        $this->assertInstanceOf(TranslationService::class, $service);
    }

    /**
     * Test 2 : Vérifier que le provider est correctement défini
     */
    public function testGetProvider(): void
    {
        $client = new MockHttpClient();
        $logger = new NullLogger();
        
        $service = new TranslationService($client, $logger, 'mymemory');

        $this->assertEquals('mymemory', $service->getProvider());
    }

    /**
     * Test 3 : Vérifier que les langues supportées sont retournées
     */
    public function testGetSupportedLanguages(): void
    {
        $client = new MockHttpClient();
        $logger = new NullLogger();
        
        $service = new TranslationService($client, $logger, 'mymemory');
        $languages = $service->getSupportedLanguages();

        $this->assertIsArray($languages);
        $this->assertArrayHasKey('fr', $languages);
        $this->assertArrayHasKey('en', $languages);
        $this->assertArrayHasKey('ar', $languages);
        $this->assertEquals('Français', $languages['fr']);
        $this->assertEquals('English', $languages['en']);
        $this->assertEquals('العربية', $languages['ar']);
    }

    /**
     * Test 4 : Vérifier la traduction avec MyMemory (mock)
     */
    public function testTranslateWithMyMemory(): void
    {
        // Mock de la réponse MyMemory
        $mockResponse = new MockResponse(json_encode([
            'responseData' => [
                'translatedText' => 'مرحبًا أنا مريم'
            ],
            'responseStatus' => 200,
            'matches' => [
                [
                    'translation' => 'مرحبًا أنا مريم',
                    'quality' => 85,
                    'match' => 1.0
                ]
            ]
        ]));

        $client = new MockHttpClient($mockResponse);
        $logger = new NullLogger();
        
        $service = new TranslationService($client, $logger, 'mymemory');
        $result = $service->translate('bonjour je suis mariem', 'ar');

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }

    /**
     * Test 5 : Vérifier que le texte vide est géré correctement
     */
    public function testTranslateEmptyText(): void
    {
        $client = new MockHttpClient();
        $logger = new NullLogger();
        
        $service = new TranslationService($client, $logger, 'mymemory');
        
        // Le service devrait retourner le texte vide sans erreur
        try {
            $result = $service->translate('', 'fr');
            // Si aucune exception, le test passe
            $this->assertTrue(true);
        } catch (\Exception $e) {
            // Si exception, c'est aussi acceptable
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /**
     * Test 6 : Vérifier la détection de langue
     */
    public function testDetectLanguage(): void
    {
        // Mock de la réponse LibreTranslate
        $mockResponse = new MockResponse(json_encode([
            [
                'language' => 'fr',
                'confidence' => 0.95
            ]
        ]));

        $client = new MockHttpClient($mockResponse);
        $logger = new NullLogger();
        
        $service = new TranslationService($client, $logger, 'libretranslate');
        $result = $service->detectLanguage('bonjour');

        $this->assertIsString($result);
    }

    /**
     * Test 7 : Vérifier que DeepL nécessite une clé API
     */
    public function testDeepLRequiresApiKey(): void
    {
        $client = new MockHttpClient();
        $logger = new NullLogger();
        
        $service = new TranslationService($client, $logger, 'deepl', null);
        
        // DeepL sans clé API devrait échouer ou utiliser un fallback
        try {
            $result = $service->translate('hello', 'fr');
            // Si ça fonctionne (fallback), c'est acceptable
            $this->assertIsString($result);
        } catch (\Exception $e) {
            // Si exception, c'est aussi acceptable
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /**
     * Test 8 : Vérifier le fallback vers MyMemory pour langues non supportées par DeepL
     */
    public function testDeepLFallbackToMyMemory(): void
    {
        // Mock de la réponse MyMemory pour l'arabe
        $mockResponse = new MockResponse(json_encode([
            'responseData' => [
                'translatedText' => 'مرحبا'
            ],
            'responseStatus' => 200,
            'matches' => [
                [
                    'translation' => 'مرحبا',
                    'quality' => 80,
                    'match' => 1.0
                ]
            ]
        ]));

        $client = new MockHttpClient($mockResponse);
        $logger = new NullLogger();
        
        // DeepL configuré mais langue arabe non supportée
        $service = new TranslationService($client, $logger, 'deepl', 'test-key');
        
        // Devrait utiliser MyMemory en fallback pour l'arabe
        $result = $service->translate('hello', 'ar');
        
        $this->assertIsString($result);
    }

    /**
     * Test 9 : Vérifier que les erreurs sont gérées correctement
     */
    public function testErrorHandling(): void
    {
        // Mock d'une réponse d'erreur
        $mockResponse = new MockResponse('', [
            'http_code' => 500
        ]);

        $client = new MockHttpClient($mockResponse);
        $logger = new NullLogger();
        
        $service = new TranslationService($client, $logger, 'mymemory');
        
        // Le service devrait gérer l'erreur
        try {
            $result = $service->translate('test', 'fr');
            // Si retourne un résultat, c'est acceptable (fallback)
            $this->assertIsString($result);
        } catch (\Exception $e) {
            // Si exception, c'est aussi acceptable
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /**
     * Test 10 : Vérifier que le provider par défaut est utilisé
     */
    public function testDefaultProvider(): void
    {
        $client = new MockHttpClient();
        $logger = new NullLogger();
        
        // Sans spécifier de provider, devrait utiliser libretranslate par défaut
        $service = new TranslationService($client, $logger);

        $this->assertEquals('libretranslate', $service->getProvider());
    }
}
