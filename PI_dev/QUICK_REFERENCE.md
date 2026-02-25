# 📚 Guide de Référence Rapide - Upload de Fichiers

## 🎯 Résumé en 30 Secondes

**Problème**: Les fichiers ne pouvaient pas être uploadés dans le chatroom.

**Solution**: 
1. ✅ Ajout du support pour plus de types de fichiers (vidéos, audio)
2. ✅ Amélioration de la détection des types MIME
3. ✅ Ajout de logs détaillés pour le débogage

**Test Rapide**:
1. Ouvrir le chatroom
2. Cliquer sur 📎
3. Sélectionner une image
4. Envoyer
5. ✅ L'image devrait apparaître!

## 📁 Fichiers Modifiés

| Fichier | Changement | Raison |
|---------|-----------|--------|
| `src/Form/MessageType.php` | Ajout types MIME vidéo/audio | Support webm pour messages vocaux |
| `src/Controller/GoalController.php` | Meilleure détection MIME | Identification correcte des fichiers webm |
| `templates/chatroom/chatroom.html.twig` | Logs détaillés | Faciliter le débogage |

## 🔍 Logs Importants

### ✅ Succès
```javascript
Attach file button clicked
Found file input by selector...
File selected: photo.png Size: 123456 Type: image/png
File preview displayed
=== Form submit started ===
Validation passed, sending request...
Response status: 200
✓ Message sent successfully!
```

### ❌ Erreur
```javascript
File input not found!
// → Rafraîchir la page

Validation failed: no content and no attachment
// → Ajouter du texte ou un fichier

Preview elements not found!
// → Vérifier que le DOM est chargé
```

## 🎨 Types de Fichiers

| Type | Extensions | Icône | Affichage |
|------|-----------|-------|-----------|
| Image | .jpg, .png, .gif, .webp | 🖼️ | Aperçu inline |
| Vidéo | .mp4, .webm, .mov | 🎥 | Carte téléchargeable |
| Audio | .mp3, .webm | 🎵 | Lecteur audio |
| PDF | .pdf | 📄 | Carte téléchargeable |
| Word | .doc, .docx | 📝 | Carte téléchargeable |
| Excel | .xls, .xlsx | 📊 | Carte téléchargeable |
| Texte | .txt | 📃 | Carte téléchargeable |

## 🚀 Commandes Rapides

```bash
# Vérifier les fichiers uploadés
dir public\uploads\messages

# Voir les logs Symfony
tail -f var/log/dev.log

# Vérifier la syntaxe
php bin/console lint:twig templates/chatroom/chatroom.html.twig

# Vider le cache
php bin/console cache:clear
```

## 🐛 Résolution Rapide

| Problème | Solution |
|----------|----------|
| Bouton ne fonctionne pas | Ctrl+F5 pour rafraîchir |
| Fichier ne s'affiche pas | Vérifier permissions dossier |
| Erreur d'envoi | Vérifier taille < 10MB |
| Type non supporté | Utiliser un type de la liste |
| Message vocal ne marche pas | Autoriser le microphone |

## 📊 Statistiques

- **Types supportés**: 15+ formats de fichiers
- **Taille max**: 10MB par fichier
- **Dossiers**: `public/uploads/messages/` et `public/uploads/voice/`
- **Validation**: Côté client ET serveur

## 🎯 Points Clés

1. **Toujours** garder la console ouverte (F12)
2. **Vérifier** les logs pour comprendre les erreurs
3. **Tester** avec des petits fichiers d'abord
4. **Rafraîchir** la page si problème (Ctrl+F5)
5. **Vérifier** les permissions des dossiers

## 📞 Support

En cas de problème, fournir:
1. ✅ Logs de la console JavaScript (copie complète)
2. ✅ Type et taille du fichier testé
3. ✅ Navigateur et version
4. ✅ Logs Symfony si disponibles

## 🎓 Ressources

- `FILE_UPLOAD_DEBUG_GUIDE.md` - Guide détaillé de débogage
- `FILE_UPLOAD_FIX_SUMMARY.md` - Résumé des corrections
- `TEST_CHECKLIST.md` - Checklist complète de tests
- `QUICK_REFERENCE.md` - Ce fichier

---

**Version**: 1.0  
**Date**: 17 février 2026  
**Status**: ✅ Production Ready
