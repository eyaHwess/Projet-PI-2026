# Intégration OpenAI - Analyse IA des messages

## Configuration

1. **Clé API** : Dans `.env.local` (déjà configuré)
   ```
   OPENAI_API_KEY=sk-proj-...
   ```

2. **Migration** : Exécuter pour ajouter la colonne `specialities` aux utilisateurs
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

## Flux

1. L'utilisateur tape un message (min. 10 caractères, max. 1000) dans le formulaire de demande de coaching
2. L'IA OpenAI analyse le message et retourne :
   - **categories** : fitness, nutrition, mental
   - **emotion** : stress, urgence, motivation ou null
3. Chaque coach reçoit un score de compatibilité
4. Les coaches sont triés par pertinence
5. Affichage : **🤖 Compatibilité IA : XX%**

## Fichiers créés/modifiés

- `src/Service/OpenAIService.php` - Appel API OpenAI
- `src/AI/CompatibilityScoreEngine.php` - Calcul du score
- `src/Entity/User.php` - Ajout `specialities` (JSON)
- `src/Controller/CoachController.php` - Endpoint recommandations
- `templates/coach/index.html.twig` - Affichage compatibilité IA

## Catégories autorisées

- **fitness** : musculation, cardio, sport, remise en forme...
- **nutrition** : alimentation, régime, diète...
- **mental** : yoga, méditation, stress, bien-être...

## Bonification du score

- +10 points par catégorie correspondante
- +3 si emotion=stress et spécialité mental
- +1 si emotion=urgence
