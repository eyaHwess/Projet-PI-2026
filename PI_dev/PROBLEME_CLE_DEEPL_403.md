# ⚠️ Erreur 403: Clé API DeepL Refusée

## 🔍 PROBLÈME DÉTECTÉ

Votre clé API DeepL retourne une erreur 403 (Forbidden):
```
DeepL API error: 403
```

Cela signifie que DeepL refuse la clé API.

## ✅ VOTRE CLÉ ACTUELLE

```
df4385c2-33de-e423-4134-ca1f7b3ea8b7:fx
```

La clé est bien formatée (se termine par `:fx`), mais DeepL la rejette.

## 🔧 CAUSES POSSIBLES

### 1. Email non confirmé ⚠️
**Le plus probable!**

DeepL exige que vous confirmiez votre email avant d'activer la clé API.

**Solution:**
1. Vérifiez votre boîte email (et spam)
2. Cherchez un email de DeepL
3. Cliquez sur le lien de confirmation
4. Attendez 2-3 minutes
5. Réessayez

### 2. Clé pas encore activée
Parfois, il faut attendre quelques minutes après la création du compte.

**Solution:**
- Attendez 5-10 minutes
- Réessayez

### 3. Mauvaise clé copiée
Vous avez peut-être copié une clé de test ou une clé incomplète.

**Solution:**
1. Retournez sur: https://www.deepl.com/fr/your-account/keys
2. Vérifiez que vous voyez "Authentication Key for DeepL API"
3. Copiez à nouveau la clé complète
4. Assurez-vous qu'elle se termine par `:fx`

### 4. Compte pas encore validé
DeepL peut demander des informations supplémentaires.

**Solution:**
1. Allez sur: https://www.deepl.com/fr/your-account/summary
2. Vérifiez s'il y a des messages d'avertissement
3. Complétez les informations demandées

## 🎯 ACTIONS IMMÉDIATES

### Étape 1: Vérifier votre email
```
1. Ouvrez votre boîte email: mariemayarn318@gmail.com
2. Cherchez un email de "DeepL" ou "noreply@deepl.com"
3. Cliquez sur le lien de confirmation
```

### Étape 2: Vérifier votre compte
```
1. Allez sur: https://www.deepl.com/fr/your-account/summary
2. Vérifiez qu'il n'y a pas de message d'erreur
3. Vérifiez que votre compte est "Active"
```

### Étape 3: Vérifier votre clé API
```
1. Allez sur: https://www.deepl.com/fr/your-account/keys
2. Copiez à nouveau la clé
3. Vérifiez qu'elle se termine par :fx
```

### Étape 4: Réessayer
Après avoir confirmé votre email, attendez 2-3 minutes puis:
```bash
php bin/console cache:clear
php bin/console app:test-translation "bonjour" en
```

## 📸 CE QUE VOUS DEVRIEZ VOIR

Sur https://www.deepl.com/fr/your-account/summary, vous devriez voir:

```
✅ Account Status: Active
✅ Email: Verified
✅ API Plan: DeepL API Free
✅ Character limit: 500,000 per month
✅ Characters used: 0 / 500,000
```

## 🔄 ALTERNATIVE: Créer une nouvelle clé

Si rien ne fonctionne:

1. Allez sur: https://www.deepl.com/fr/your-account/keys
2. Supprimez l'ancienne clé (si possible)
3. Créez une nouvelle clé
4. Copiez la nouvelle clé
5. Mettez à jour `.env`

## 💡 PENDANT CE TEMPS

En attendant que DeepL soit activé, votre système utilise automatiquement MyMemory comme fallback.

Les traductions fonctionnent, mais avec une qualité réduite (60% au lieu de 98%).

## ❓ BESOIN D'AIDE?

Si le problème persiste après avoir:
- ✅ Confirmé votre email
- ✅ Attendu 10 minutes
- ✅ Vérifié votre compte

Alors:
1. Contactez le support DeepL: https://support.deepl.com
2. Ou créez un nouveau compte avec un autre email

## 🧪 TEST RAPIDE

Une fois que vous pensez que c'est résolu:

```bash
# Vider le cache
php bin/console cache:clear

# Tester
php bin/console app:test-translation "hello" fr
```

**Résultat attendu:**
```
✅ Traduction réussie!
Texte original: hello
Traduction: bonjour
```

**Si vous voyez encore "DeepL API error: 403":**
→ Votre email n'est pas confirmé ou votre compte n'est pas activé.

---

## 📋 CHECKLIST

- [ ] Email confirmé (vérifiez votre boîte email)
- [ ] Compte actif (vérifiez sur deepl.com/account/summary)
- [ ] Clé API copiée correctement (se termine par :fx)
- [ ] Attendu 5-10 minutes après création du compte
- [ ] Cache vidé: `php bin/console cache:clear`
- [ ] Test effectué: `php bin/console app:test-translation "hello" fr`

---

**Prochaine étape:** Vérifiez votre email et confirmez votre compte DeepL!
