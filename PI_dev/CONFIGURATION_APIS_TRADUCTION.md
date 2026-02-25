# 🌍 Configuration des APIs de Traduction

## ✅ Statut: IMPLÉMENTÉ

Le système supporte maintenant 3 APIs de traduction:
1. **LibreTranslate** (gratuit, par défaut)
2. **DeepL** (meilleure qualité, freemium)
3. **Google Translate** (payant, très fiable)

---

## 🎯 Choix de l'API

### LibreTranslate (Par Défaut) ✅
- **Prix**: Gratuit
- **Qualité**: Bonne
- **Limite**: Raisonnable pour usage académique
- **Configuration**: Aucune (fonctionne immédiatement)

### DeepL (Recommandé) ⭐
- **Prix**: Gratuit jusqu'à 500,000 caractères/mois
- **Qualité**: Excellente (meilleure que Google pour certaines langues)
- **Limite**: 500,000 caractères/mois (gratuit)
- **Configuration**: Clé API nécessaire

### Google Translate
- **Prix**: Payant ($20 par million de caractères)
- **Qualité**: Très bonne
- **Limite**: Selon votre budget
- **Configuration**: Clé API + compte Google Cloud

---

## 🚀 Configuration

### Option 1: LibreTranslate (Aucune Configuration)

**C'est déjà configuré!** Rien à faire.

```env
TRANSLATION_PROVIDER=libretranslate
```

---

### Option 2: DeepL (Recommandé)

#### Étape 1: Créer un Compte DeepL

1. Allez sur: https://www.deepl.com/pro-api
2. Cliquez sur "Sign up for free"
3. Remplissez le formulaire
4. Confirmez votre email

#### Étape 2: Obtenir la Clé API

1. Connectez-vous à: https://www.deepl.com/account/summary
2. Allez dans "Account" → "API Keys"
3. Copiez votre "Authentication Key"

#### Étape 3: Configurer dans .env

Ouvrez `.env` et modifiez:

```env
TRANSLATION_PROVIDER=deepl
DEEPL_API_KEY=votre-clé-api-ici
```

**Exemple**:
```env
TRANSLATION_PROVIDER=deepl
DEEPL_API_KEY=abc123def456-ghi789:fx
```

#### Étape 4: Vider le Cache

```bash
php bin/console cache:clear
```

**C'est tout!** DeepL est maintenant actif.

---

### Option 3: Google Translate

#### Étape 1: Créer un Projet Google Cloud

1. Allez sur: https://console.cloud.google.com/
2. Créez un nouveau projet
3. Activez "Cloud Translation API"

#### Étape 2: Créer une Clé API

1. Allez dans "APIs & Services" → "Credentials"
2. Cliquez sur "Create Credentials" → "API Key"
3. Copiez la clé générée

#### Étape 3: Activer la Facturation

Google Translate nécessite un compte de facturation actif.

#### Étape 4: Configurer dans .env

```env
TRANSLATION_PROVIDER=google
GOOGLE_API_KEY=votre-clé-api-ici
```

**Exemple**:
```env
TRANSLATION_PROVIDER=google
GOOGLE_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

#### Étape 5: Vider le Cache

```bash
php bin/console cache:clear
```

---

## 📊 Comparaison des APIs

| Critère | LibreTranslate | DeepL | Google Translate |
|---------|---------------|-------|------------------|
| **Prix** | Gratuit | Gratuit (500k/mois) | $20/million |
| **Qualité** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Vitesse** | Moyenne | Rapide | Très rapide |
| **Langues** | 30+ | 30+ | 100+ |
| **Configuration** | Aucune | Clé API | Clé API + Facturation |
| **Limite gratuite** | Illimitée | 500k caractères/mois | Aucune |
| **Recommandé pour** | Développement | Production | Entreprise |

---

## 🧪 Test des APIs

### Test LibreTranslate (Par Défaut)

```bash
# Aucune configuration nécessaire
# Ouvrez simplement le chatroom et testez
```

### Test DeepL

1. Configurez la clé API dans `.env`
2. Videz le cache: `php bin/console cache:clear`
3. Ouvrez le chatroom
4. Envoyez un message: "Bonjour tout le monde!"
5. Cliquez sur "🌍 Traduire"
6. Vérifiez la qualité de la traduction

### Test Google Translate

1. Configurez la clé API dans `.env`
2. Videz le cache: `php bin/console cache:clear`
3. Testez comme pour DeepL

---

## 🔧 Code Implémenté

### TranslationService.php

Le service supporte maintenant 3 providers:

```php
public function translate(string $text, string $target = 'en', string $source = 'auto'): string
{
    try {
        return match($this->provider) {
            'deepl' => $this->translateWithDeepL($text, $target, $source),
            'google' => $this->translateWithGoogle($text, $target, $source),
            default => $this->translateWithLibreTranslate($text, $target, $source),
        };
    } catch (\Exception $e) {
        // Fallback vers LibreTranslate en cas d'erreur
        return $this->translateWithLibreTranslate($text, $target, $source);
    }
}
```

### Méthodes Privées

#### LibreTranslate
```php
private function translateWithLibreTranslate(string $text, string $target, string $source): string
{
    $response = $this->client->request('POST', 'https://libretranslate.de/translate', [
        'json' => [
            'q' => $text,
            'source' => $source,
            'target' => $target,
            'format' => 'text',
        ],
        'timeout' => 10,
    ]);

    $data = $response->toArray();
    return $data['translatedText'] ?? 'Erreur de traduction';
}
```

#### DeepL
```php
private function translateWithDeepL(string $text, string $target, string $source): string
{
    if (!$this->deeplApiKey) {
        throw new \Exception('Clé API DeepL non configurée');
    }

    $targetLang = strtoupper($target);
    if ($targetLang === 'EN') {
        $targetLang = 'EN-US';
    }

    $url = 'https://api-free.deepl.com/v2/translate';
    
    $response = $this->client->request('POST', $url, [
        'headers' => [
            'Authorization' => 'DeepL-Auth-Key ' . $this->deeplApiKey,
        ],
        'body' => [
            'text' => $text,
            'target_lang' => $targetLang,
            'source_lang' => $source === 'auto' ? null : strtoupper($source),
        ],
        'timeout' => 10,
    ]);

    $data = $response->toArray();
    return $data['translations'][0]['text'] ?? 'Erreur de traduction';
}
```

#### Google Translate
```php
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
```

---

## 🎯 Langues Supportées

### Toutes les APIs
- 🇬🇧 English (en)
- 🇫🇷 Français (fr)
- 🇪🇸 Español (es)
- 🇩🇪 Deutsch (de)
- 🇮🇹 Italiano (it)
- 🇵🇹 Português (pt)
- 🇳🇱 Nederlands (nl)
- 🇵🇱 Polski (pl)
- 🇹🇷 Türkçe (tr)
- 🇰🇷 한국어 (ko)

### LibreTranslate & Google (Plus de langues)
- 🇸🇦 العربية (ar)
- 🇨🇳 中文 (zh)
- 🇯🇵 日本語 (ja)
- 🇷🇺 Русский (ru)
- Et 20+ autres langues

---

## 🐛 Troubleshooting

### Erreur: "Clé API DeepL non configurée"

**Solution**:
1. Vérifiez que `DEEPL_API_KEY` est défini dans `.env`
2. Vérifiez qu'il n'y a pas d'espaces avant/après la clé
3. Videz le cache: `php bin/console cache:clear`

### Erreur: "Clé API Google non configurée"

**Solution**:
1. Vérifiez que `GOOGLE_API_KEY` est défini dans `.env`
2. Vérifiez que l'API Cloud Translation est activée
3. Vérifiez que la facturation est activée
4. Videz le cache: `php bin/console cache:clear`

### Traduction lente

**Solutions**:
- LibreTranslate peut être lent parfois (serveur public)
- Passez à DeepL ou Google pour plus de rapidité
- Le cache rend les traductions suivantes instantanées

### Erreur 403 (Forbidden)

**DeepL**:
- Vérifiez que votre clé API est valide
- Vérifiez que vous n'avez pas dépassé la limite gratuite

**Google**:
- Vérifiez que l'API est activée
- Vérifiez que la facturation est configurée
- Vérifiez les restrictions de la clé API

---

## 💡 Recommandations

### Pour le Développement
```env
TRANSLATION_PROVIDER=libretranslate
```
- Gratuit
- Aucune configuration
- Suffisant pour les tests

### Pour la Production (Projet Académique)
```env
TRANSLATION_PROVIDER=deepl
DEEPL_API_KEY=votre-clé
```
- Gratuit jusqu'à 500k caractères/mois
- Excellente qualité
- Facile à configurer

### Pour la Production (Entreprise)
```env
TRANSLATION_PROVIDER=google
GOOGLE_API_KEY=votre-clé
```
- Très fiable
- Support de nombreuses langues
- Rapide

---

## 📋 Checklist de Configuration

### LibreTranslate (Par Défaut)
- [x] Aucune configuration nécessaire
- [x] Fonctionne immédiatement

### DeepL
- [ ] Créer un compte sur deepl.com/pro-api
- [ ] Obtenir la clé API
- [ ] Ajouter `DEEPL_API_KEY` dans `.env`
- [ ] Définir `TRANSLATION_PROVIDER=deepl`
- [ ] Vider le cache
- [ ] Tester

### Google Translate
- [ ] Créer un projet Google Cloud
- [ ] Activer Cloud Translation API
- [ ] Configurer la facturation
- [ ] Créer une clé API
- [ ] Ajouter `GOOGLE_API_KEY` dans `.env`
- [ ] Définir `TRANSLATION_PROVIDER=google`
- [ ] Vider le cache
- [ ] Tester

---

## 🎉 Résultat

Vous avez maintenant le choix entre 3 APIs de traduction professionnelles!

**Par défaut**: LibreTranslate fonctionne sans configuration.

**Pour améliorer**: Configurez DeepL (gratuit, meilleure qualité).

**Pour l'entreprise**: Configurez Google Translate (payant, très fiable).

---

**Tout est prêt!** Choisissez votre API et commencez à traduire! 🌍✨
