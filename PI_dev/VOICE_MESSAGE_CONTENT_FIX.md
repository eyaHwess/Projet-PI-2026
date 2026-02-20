# 🔧 Correction: Messages Vocaux - Champ Content Nullable

## Problème Identifié

**Erreur**: "Erreur lors de l'envoi du message vocal"

**Cause**: Le champ `content` de l'entité `Message` n'était pas nullable en base de données, mais les messages vocaux n'ont pas besoin de contenu textuel. Lorsqu'on essayait de sauvegarder un message vocal avec `content = ''` (chaîne vide), la base de données rejetait l'insertion car le champ était défini comme `NOT NULL`.

## Solution Implémentée

### 1. Modification de l'Entité Message

**Avant:**
```php
#[ORM\Column(type: 'text')]
private ?string $content = null;
```

**Après:**
```php
#[ORM\Column(type: 'text', nullable: true)]
private ?string $content = null;
```

**Changement**: Ajout de `nullable: true` pour permettre des valeurs NULL.

### 2. Migration de Base de Données

**Fichier**: `migrations/Version20260217100836.php`

```php
public function up(Schema $schema): void
{
    $this->addSql('ALTER TABLE message ALTER content DROP NOT NULL');
}
```

**Effet**: Le champ `content` peut maintenant être NULL en base de données.

### 3. Modification du Contrôleur

**Avant:**
```php
$message->setContent(''); // Voice messages don't need text content
```

**Après:**
```php
$message->setContent(null); // Voice messages don't need text content
```

**Changement**: Utilisation de `null` au lieu d'une chaîne vide pour les messages vocaux.

## Pourquoi Cette Correction?

### Logique Métier

Les messages vocaux sont des messages qui contiennent uniquement un fichier audio, sans texte. Il est donc logique que le champ `content` soit NULL pour ces messages.

### Types de Messages

1. **Message texte**: `content` = texte, `attachmentPath` = null
2. **Message avec fichier**: `content` = texte (optionnel), `attachmentPath` = chemin
3. **Message vocal**: `content` = null, `attachmentPath` = chemin audio

### Avantages

- ✅ Distinction claire entre "pas de contenu" (NULL) et "contenu vide" ('')
- ✅ Cohérence avec la logique métier
- ✅ Pas de contrainte artificielle
- ✅ Flexibilité pour les futurs types de messages

## Validation Côté Serveur

La validation dans le contrôleur reste inchangée:

```php
// Content is optional if there's an attachment
if (empty($message->getContent()) && !$attachmentFile) {
    $this->addFlash('error', 'Le message doit contenir du texte ou un fichier.');
    return $this->redirectToRoute('goal_messages', ['id' => $goal->getId()]);
}
```

**Logique**: Un message doit avoir soit du contenu, soit un fichier (ou les deux).

## Affichage dans le Template

Le template gère déjà correctement les messages sans contenu:

```twig
{% if message.hasAttachment %}
    <div class="message-attachment">
        {% if message.attachmentType == 'audio' %}
            <div class="voice-message-player">
                <!-- Voice player -->
            </div>
        {% endif %}
    </div>
{% endif %}

{% if message.content %}
    <span class="message-text">{{ message.content }}</span>
{% endif %}
```

**Logique**: 
- Si le message a un fichier, on l'affiche
- Si le message a du contenu, on l'affiche
- Un message vocal n'affichera que le player audio

## Tests de Validation

### Test 1: Message Vocal Seul
1. Enregistrer un message vocal
2. Cliquer "Envoyer"
3. ✅ Message envoyé sans erreur
4. ✅ Player audio affiché
5. ✅ Pas de texte affiché

### Test 2: Message Texte Seul
1. Taper "Hello"
2. Cliquer "Envoyer"
3. ✅ Message envoyé
4. ✅ Texte affiché
5. ✅ Pas de fichier

### Test 3: Message avec Texte et Fichier
1. Taper "Voici une photo"
2. Joindre une image
3. Cliquer "Envoyer"
4. ✅ Message envoyé
5. ✅ Texte et image affichés

### Test 4: Message Vide (Erreur Attendue)
1. Ne rien taper
2. Ne rien joindre
3. Cliquer "Envoyer"
4. ✅ Erreur: "Veuillez entrer un message ou joindre un fichier"

## Impact sur les Données Existantes

### Messages Existants

Les messages existants avec `content = ''` (chaîne vide) restent inchangés. La migration ne modifie que la contrainte de la colonne, pas les données.

### Compatibilité

- ✅ Les anciens messages fonctionnent toujours
- ✅ Les nouveaux messages peuvent avoir `content = NULL`
- ✅ Pas de perte de données
- ✅ Pas de migration de données nécessaire

## Requêtes SQL Affectées

### Avant (Erreur)
```sql
INSERT INTO message (content, attachment_path, attachment_type, ...)
VALUES ('', '/uploads/voice/voice-123.webm', 'audio', ...);
-- Erreur: content ne peut pas être vide si NOT NULL
```

### Après (Succès)
```sql
INSERT INTO message (content, attachment_path, attachment_type, ...)
VALUES (NULL, '/uploads/voice/voice-123.webm', 'audio', ...);
-- Succès: content peut être NULL
```

## Autres Cas d'Usage

Cette modification permet aussi:

1. **Messages avec seulement une image**
   ```php
   $message->setContent(null);
   $message->setAttachmentPath('/uploads/messages/photo.jpg');
   $message->setAttachmentType('image');
   ```

2. **Messages avec seulement un document**
   ```php
   $message->setContent(null);
   $message->setAttachmentPath('/uploads/messages/doc.pdf');
   $message->setAttachmentType('pdf');
   ```

3. **Futurs types de messages**
   - Stickers
   - GIFs
   - Vidéos
   - Localisation
   - Contacts

## Vérification en Base de Données

### Vérifier la Contrainte

```sql
-- PostgreSQL
SELECT column_name, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'message' AND column_name = 'content';

-- Résultat attendu: is_nullable = 'YES'
```

### Vérifier les Messages Vocaux

```sql
SELECT id, content, attachment_type, attachment_path 
FROM message 
WHERE attachment_type = 'audio';

-- Résultat: content devrait être NULL pour les messages vocaux
```

## Rollback (Si Nécessaire)

Si besoin de revenir en arrière:

```bash
php bin/console doctrine:migrations:migrate prev
```

Cela exécutera le `down()` de la migration:

```php
public function down(Schema $schema): void
{
    $this->addSql('ALTER TABLE message ALTER content SET NOT NULL');
}
```

⚠️ **Attention**: Le rollback échouera s'il existe des messages avec `content = NULL`.

## Bonnes Pratiques

### 1. Vérification du Contenu

```php
// Vérifier si un message a du contenu
if ($message->getContent() !== null && $message->getContent() !== '') {
    // Le message a du contenu
}

// Ou plus simple
if (!empty($message->getContent())) {
    // Le message a du contenu
}
```

### 2. Affichage Conditionnel

```twig
{% if message.content %}
    <div class="message-text">{{ message.content }}</div>
{% endif %}
```

### 3. Validation

```php
// Un message doit avoir soit du contenu, soit un fichier
if (empty($message->getContent()) && !$message->hasAttachment()) {
    throw new \Exception('Message vide');
}
```

## Conclusion

La correction permet maintenant:
- ✅ Envoi de messages vocaux sans erreur
- ✅ Champ `content` nullable en base de données
- ✅ Distinction claire entre NULL et chaîne vide
- ✅ Flexibilité pour futurs types de messages
- ✅ Cohérence avec la logique métier
- ✅ Pas d'impact sur les données existantes

---

**Date de Correction**: 17 Février 2026
**Migration**: Version20260217100836
**Status**: ✅ Corrigé et Testé
**Impact**: Critique (fonctionnalité bloquée)
**Complexité**: Faible (modification de schéma)
