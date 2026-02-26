<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class TranslationService
{
    private string $provider;
    private ?string $deeplApiKey;
    private ?string $googleApiKey;

    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        string $translationProvider = 'libretranslate',
        ?string $deeplApiKey = null,
        ?string $googleApiKey = null
    ) {
        $this->provider = $translationProvider;
        $this->deeplApiKey = $deeplApiKey;
        $this->googleApiKey = $googleApiKey;
    }

    /**
     * Traduit un texte vers la langue cible
     * 
     * @param string $text Texte à traduire
     * @param string $target Langue cible (en, fr, es, de, etc.)
     * @param string $source Langue source (auto pour détection automatique)
     * @return string Texte traduit
     */
    public function translate(string $text, string $target = 'en', string $source = 'auto'): string
    {
        try {
            // Utiliser le provider configuré, avec MyMemory comme fallback
            $result = match($this->provider) {
                'deepl' => $this->translateWithDeepL($text, $target, $source),
                'google' => $this->translateWithGoogle($text, $target, $source),
                'libretranslate' => $this->translateWithLibreTranslate($text, $target, $source),
                'mymemory' => $this->translateWithMyMemory($text, $target, $source),
                default => $this->translateWithMyMemory($text, $target, $source),
            };
            
            // Si le résultat contient une erreur, essayer MyMemory en fallback
            if (str_starts_with($result, 'Erreur')) {
                $this->logger->warning('Provider failed, trying MyMemory fallback', [
                    'provider' => $this->provider
                ]);
                
                // Ne pas réessayer MyMemory si c'était déjà le provider
                if ($this->provider !== 'mymemory') {
                    $result = $this->translateWithMyMemory($text, $target, $source);
                }
            }
            
            // Améliorer la traduction avec post-traitement (sauf pour DeepL qui est déjà excellent)
            if ($this->provider !== 'deepl' && !str_starts_with($result, 'Erreur')) {
                $result = $this->improveTranslation($result, $target, $source);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur traduction', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100),
                'target' => $target
            ]);

            // Essayer MyMemory en dernier recours (sauf si c'était déjà le provider)
            if ($this->provider !== 'mymemory') {
                try {
                    $result = $this->translateWithMyMemory($text, $target, $source);
                    // Améliorer la traduction MyMemory
                    return $this->improveTranslation($result, $target, $source);
                } catch (\Exception $fallbackError) {
                    $this->logger->error('MyMemory fallback failed', [
                        'error' => $fallbackError->getMessage()
                    ]);
                }
            }
            
            return 'Erreur: Service de traduction temporairement indisponible';
        }
    }

    /**
     * Traduction avec LibreTranslate (gratuit)
     * Utilise l'instance publique libretranslate.de (sans API key)
     */
    private function translateWithLibreTranslate(string $text, string $target, string $source): string
    {
        try {
            // Utiliser libretranslate.de qui est gratuit et sans API key
            $url = 'https://libretranslate.de/translate';
            
            $response = $this->client->request('POST', $url, [
                'json' => [
                    'q' => $text,
                    'source' => $source === 'auto' ? 'auto' : $source,
                    'target' => $target,
                    'format' => 'text',
                ],
                'timeout' => 8,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ],
            ]);

            $data = $response->toArray();
            
            if (isset($data['translatedText']) && !empty($data['translatedText'])) {
                return $data['translatedText'];
            }
            
            $this->logger->warning('LibreTranslate returned empty translation');
            throw new \Exception('LibreTranslate: Empty translation');
            
        } catch (\Exception $e) {
            $this->logger->error('LibreTranslate failed', [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100)
            ]);
            throw $e;
        }
    }

    /**
     * Traduction avec MyMemory (gratuit, sans API key)
     * Limite: 1000 mots/jour par IP
     * MyMemory ne supporte pas "auto" pour la source : il faut un langpair explicite (ex: FR|EN).
     */
    private function translateWithMyMemory(string $text, string $target, string $source): string
    {
        $url = 'https://api.mymemory.translated.net/get';
        
        $cleanText = trim($text);
        if ($cleanText === '') {
            return $text;
        }

        $sourceLang = $source === 'auto' ? $this->guessSourceLanguage($cleanText, $target) : $source;
        $sourceLang = strtolower(substr((string)$sourceLang, 0, 2));
        $targetLang = strtolower(substr((string)$target, 0, 2));

        // Si même langue, pas besoin d'appeler l'API
        if ($sourceLang === $targetLang) {
            return $text;
        }

        // MyMemory recommande souvent des codes en majuscules: EN|FR
        $langpair = strtoupper($sourceLang) . '|' . strtoupper($targetLang);
        
        try {
            $response = $this->client->request('GET', $url, [
                'query' => [
                    'q' => $text,
                    'langpair' => $langpair,
                ],
                'timeout' => 15,
            ]);

            $data = $response->toArray();
            
            if (isset($data['responseData']['translatedText'])) {
                $translated = trim($data['responseData']['translatedText']);
                
                // Vérifier que la traduction n'est pas vide et différente du texte original
                if ($translated !== '' && $translated !== $text) {
                    return $translated;
                }
                
                // Si la traduction est identique, retourner le texte original
                if ($translated === $text) {
                    return $text;
                }
            }
            
            if (isset($data['responseStatus']) && (int)$data['responseStatus'] !== 200) {
                $errorMsg = $data['responseDetails'] ?? 'Unknown error';
                $this->logger->warning('MyMemory error', ['error' => $errorMsg]);
                throw new \Exception('MyMemory: ' . $errorMsg);
            }
            
            // Si aucune traduction valide n'est trouvée, retourner le texte original
            return $text;
            
        } catch (\Exception $e) {
            $this->logger->error('MyMemory request failed', [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100)
            ]);
            throw $e;
        }
    }

    private function guessSourceLanguage(string $text, string $target): string
    {
        // Heuristique légère (pas parfaite) pour avoir un langpair valide avec MyMemory.
        if (preg_match('/\p{Arabic}/u', $text)) {
            return 'ar';
        }
        if (preg_match('/\p{Cyrillic}/u', $text)) {
            return 'ru';
        }
        if (preg_match('/[àâçéèêëîïôùûüÿœ]/iu', $text)) {
            return 'fr';
        }

        $t = strtolower(substr($target, 0, 2));
        return match ($t) {
            'en' => 'fr',
            'fr' => 'en',
            default => 'en',
        };
    }

    /**
     * Traduction avec DeepL (meilleure qualité)
     * Version FREE API - 500,000 caractères/mois
     * Documentation: https://www.deepl.com/docs-api
     */
    private function translateWithDeepL(string $text, string $target, string $source): string
    {
        // Vérifier que la clé API est configurée
        if (!$this->deeplApiKey || trim($this->deeplApiKey) === '' || $this->deeplApiKey === 'votre_cle_deepl_ici') {
            $this->logger->error('DeepL API key not configured');
            throw new \Exception('Clé API DeepL non configurée. Obtenez-en une sur https://www.deepl.com/pro-api');
        }

        try {
            // Normaliser le code de langue cible (DeepL est strict)
            $targetLang = strtoupper(substr($target, 0, 2));
            
            // DeepL exige EN-US ou EN-GB pour l'anglais
            if ($targetLang === 'EN') {
                $targetLang = 'EN-US';
            }
            
            // Préparer les paramètres
            $params = [
                'text' => $text,
                'target_lang' => $targetLang,
            ];
            
            // Ajouter la langue source si spécifiée (optionnel pour DeepL)
            if ($source !== 'auto' && $source !== null && $source !== '') {
                $sourceLang = strtoupper(substr($source, 0, 2));
                // DeepL accepte EN sans suffixe pour la source
                $params['source_lang'] = $sourceLang;
            }
            
            // Appel à l'API DeepL FREE
            $response = $this->client->request('POST', 'https://api-free.deepl.com/v2/translate', [
                'headers' => [
                    'Authorization' => 'DeepL-Auth-Key ' . $this->deeplApiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $params,
                'timeout' => 10,
            ]);
            
            // Vérifier le code de statut
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $this->logger->error('DeepL API error', [
                    'status' => $statusCode,
                    'text' => substr($text, 0, 100)
                ]);
                throw new \Exception('DeepL API error: ' . $statusCode);
            }
            
            // Parser la réponse
            $data = $response->toArray();
            
            // Vérifier que la traduction existe
            if (!isset($data['translations']) || !is_array($data['translations']) || empty($data['translations'])) {
                $this->logger->error('DeepL returned no translations', ['response' => $data]);
                throw new \Exception('DeepL: Aucune traduction retournée');
            }
            
            $translation = $data['translations'][0]['text'] ?? null;
            
            if ($translation === null || trim($translation) === '') {
                $this->logger->error('DeepL returned empty translation');
                throw new \Exception('DeepL: Traduction vide');
            }
            
            // Log succès pour monitoring
            $this->logger->info('DeepL translation successful', [
                'source_lang' => $data['translations'][0]['detected_source_language'] ?? 'unknown',
                'target_lang' => $targetLang,
                'text_length' => strlen($text)
            ]);
            
            return $translation;
            
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            // Erreur 4xx (clé invalide, quota dépassé, etc.)
            $this->logger->error('DeepL client error', [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100)
            ]);
            
            // Messages d'erreur plus explicites
            if (str_contains($e->getMessage(), '403')) {
                throw new \Exception('DeepL: Clé API invalide. Vérifiez votre clé sur https://www.deepl.com/account/summary');
            } elseif (str_contains($e->getMessage(), '456')) {
                throw new \Exception('DeepL: Quota dépassé. Limite: 500,000 caractères/mois');
            } else {
                throw new \Exception('DeepL: Erreur client - ' . $e->getMessage());
            }
            
        } catch (\Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface $e) {
            // Erreur 5xx (serveur DeepL)
            $this->logger->error('DeepL server error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('DeepL: Serveur temporairement indisponible');
            
        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            // Erreur réseau
            $this->logger->error('DeepL transport error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('DeepL: Erreur de connexion');
            
        } catch (\Exception $e) {
            // Autres erreurs
            $this->logger->error('DeepL unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Traduction avec Google Translate
     */
    private function translateWithGoogle(string $text, string $target, string $source): string
    {
        if (!$this->googleApiKey) {
            throw new \Exception('Clé API Google non configurée');
        }

        $url = 'https://translation.googleapis.com/language/translate/v2';
        
        $params = [
            'q' => $text,
            'target' => $target,
            'key' => $this->googleApiKey,
            'format' => 'text',
        ];

        if ($source !== 'auto') {
            $params['source'] = $source;
        }

        $response = $this->client->request('POST', $url, [
            'json' => $params,
            'timeout' => 10,
        ]);

        $data = $response->toArray();
        return $data['data']['translations'][0]['translatedText'] ?? 'Erreur de traduction';
    }

    /**
     * Détecte la langue d'un texte
     */
    public function detectLanguage(string $text): string
    {
        try {
            return match($this->provider) {
                'google' => $this->detectLanguageWithGoogle($text),
                default => $this->detectLanguageWithLibreTranslate($text),
            };
        } catch (\Exception $e) {
            $this->logger->error('Erreur détection langue', [
                'error' => $e->getMessage()
            ]);

            return 'unknown';
        }
    }

    /**
     * Détection de langue avec LibreTranslate
     */
    private function detectLanguageWithLibreTranslate(string $text): string
    {
        $response = $this->client->request('POST', 'https://libretranslate.de/detect', [
            'json' => [
                'q' => $text,
            ],
            'timeout' => 5,
        ]);

        $data = $response->toArray();

        if (isset($data[0]['language'])) {
            return $data[0]['language'];
        }

        return 'unknown';
    }

    /**
     * Détection de langue avec Google
     */
    private function detectLanguageWithGoogle(string $text): string
    {
        if (!$this->googleApiKey) {
            return 'unknown';
        }

        $url = 'https://translation.googleapis.com/language/translate/v2/detect';
        
        $response = $this->client->request('POST', $url, [
            'json' => [
                'q' => $text,
                'key' => $this->googleApiKey,
            ],
            'timeout' => 5,
        ]);

        $data = $response->toArray();
        return $data['data']['detections'][0][0]['language'] ?? 'unknown';
    }

    /**
     * Retourne les langues supportées
     */
    public function getSupportedLanguages(): array
    {
        return [
            'en' => 'English',
            'fr' => 'Français',
            'es' => 'Español',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ar' => 'العربية',
        ];
    }

    /**
     * Retourne le provider actuel
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Améliore la qualité des traductions avec des corrections post-traitement
     * Utile pour MyMemory et autres providers de qualité moyenne
     */
    private function improveTranslation(string $text, string $targetLang, string $sourceLang = 'auto'): string
    {
        // Corrections pour le français
        if ($targetLang === 'fr') {
            $corrections = [
                // Expressions courantes anglais → français
                'Je suis bon' => 'Je vais bien',
                'je suis bon' => 'je vais bien',
                'Comment êtes-vous' => 'Comment allez-vous',
                'comment êtes-vous' => 'comment allez-vous',
                'Voir vous plus tard' => 'À plus tard',
                'voir vous plus tard' => 'à plus tard',
                'Quoi est en haut' => 'Quoi de neuf',
                'quoi est en haut' => 'quoi de neuf',
                'Pas inquiétudes' => 'Pas de souci',
                'pas inquiétudes' => 'pas de souci',
                'Prendre soin' => 'Prends soin de toi',
                'prendre soin' => 'prends soin de toi',
                'Touchons la base' => 'Faisons le point',
                'touchons la base' => 'faisons le point',
                'C\'est un morceau de gâteau' => 'C\'est du gâteau',
                'c\'est un morceau de gâteau' => 'c\'est du gâteau',
                'Casser une jambe' => 'Bonne chance',
                'casser une jambe' => 'bonne chance',
                'Je suis cassé' => 'Je suis fauché',
                'je suis cassé' => 'je suis fauché',
                'Je suis sur mon chemin' => 'Je suis en route',
                'je suis sur mon chemin' => 'je suis en route',
                'Il pleut des chats et des chiens' => 'Il pleut des cordes',
                'il pleut des chats et des chiens' => 'il pleut des cordes',
                
                // Salutations et présentations
                'Je suis' => 'Je m\'appelle',
                'je suis' => 'je m\'appelle',
                'Mon nom est' => 'Je m\'appelle',
                'mon nom est' => 'je m\'appelle',
                
                // Corrections grammaticales
                'envoyer moi' => 'm\'envoyer',
                'Envoyer moi' => 'M\'envoyer',
                'rencontrer vous' => 'vous rencontrer',
                'Rencontrer vous' => 'Vous rencontrer',
                'voir vous' => 'vous voir',
                'Voir vous' => 'Vous voir',
                'donner moi' => 'me donner',
                'Donner moi' => 'Me donner',
                'appeler moi' => 'm\'appeler',
                'Appeler moi' => 'M\'appeler',
                
                // Temps et conjugaisons
                'pour mois' => 'depuis des mois',
                'pour jours' => 'depuis des jours',
                'pour années' => 'depuis des années',
                'pour semaines' => 'depuis des semaines',
                'pour heures' => 'depuis des heures',
                
                // Expressions de temps
                'dans le matin' => 'le matin',
                'dans l\'après-midi' => 'l\'après-midi',
                'dans le soir' => 'le soir',
                'dans la nuit' => 'la nuit',
                
                // Mots courants mal traduits
                'actuellement' => 'en ce moment',
                'éventuellement' => 'peut-être',
                'finalement' => 'enfin',
            ];
            
            foreach ($corrections as $wrong => $correct) {
                // Utiliser une regex pour matcher les mots entiers
                $pattern = '/\b' . preg_quote($wrong, '/') . '\b/iu';
                $text = preg_replace($pattern, $correct, $text);
            }
        }
        
        // Corrections pour l'anglais
        if ($targetLang === 'en') {
            $corrections = [
                // Expressions courantes français → anglais
                'I am good' => 'I\'m fine',
                'How are you' => 'How are you doing',
                'See you more late' => 'See you later',
                'What is up' => 'What\'s up',
                'Not worries' => 'No worries',
                
                // Salutations
                'I am' => 'My name is',
                'My name is' => 'I\'m',
                
                // Corrections grammaticales
                'send me the' => 'send me the',
                'meet you' => 'meet you',
                'see you' => 'see you',
                'call me' => 'call me',
                
                // Temps
                'for months' => 'for months',
                'for days' => 'for days',
                'for years' => 'for years',
            ];
            
            foreach ($corrections as $wrong => $correct) {
                $pattern = '/\b' . preg_quote($wrong, '/') . '\b/iu';
                $text = preg_replace($pattern, $correct, $text);
            }
        }
        
        // Corrections pour l'allemand
        if ($targetLang === 'de') {
            // Corrections spécifiques pour l'allemand
            $corrections = [
                'Guten Tag ich bin' => 'Guten Tag, ich heiße',
                'guten tag ich bin' => 'guten tag, ich heiße',
                'Hallo ich bin' => 'Hallo, ich heiße',
                'hallo ich bin' => 'hallo, ich heiße',
            ];
            
            foreach ($corrections as $wrong => $correct) {
                $pattern = '/\b' . preg_quote($wrong, '/') . '\b/iu';
                $text = preg_replace($pattern, $correct, $text);
            }
        }
        
        // Corrections pour l'espagnol
        if ($targetLang === 'es') {
            $corrections = [
                'Hola yo soy' => 'Hola, me llamo',
                'hola yo soy' => 'hola, me llamo',
                'Buenos días yo soy' => 'Buenos días, me llamo',
                'buenos días yo soy' => 'buenos días, me llamo',
            ];
            
            foreach ($corrections as $wrong => $correct) {
                $pattern = '/\b' . preg_quote($wrong, '/') . '\b/iu';
                $text = preg_replace($pattern, $correct, $text);
            }
        }
        
        return $text;
    }
}
