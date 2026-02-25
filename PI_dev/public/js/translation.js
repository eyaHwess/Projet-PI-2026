/**
 * Système de traduction pour le chatroom
 */

// Variables globales
window.translationMenus = {};

/**
 * Basculer le menu de traduction
 */
window.toggleTranslateMenu = function(messageId) {
    console.log('toggleTranslateMenu appelée:', messageId);
    
    const menu = document.getElementById('translateMenu' + messageId);
    if (!menu) {
        console.warn('Menu non trouvé (normal si bouton simple):', 'translateMenu' + messageId);
        return;
    }
    
    // Fermer tous les autres menus
    document.querySelectorAll('.translate-menu.show').forEach(m => {
        if (m.id !== 'translateMenu' + messageId) {
            m.classList.remove('show');
        }
    });
    
    // Basculer ce menu
    menu.classList.toggle('show');
    console.log('Menu ouvert:', menu.classList.contains('show'));
};

/**
 * Traduire un message
 */
window.translateMessageTo = function(event, messageId, targetLang, langName) {
    console.log('translateMessageTo appelée:', messageId, targetLang, langName);
    
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Fermer le menu s'il existe
    const menu = document.getElementById('translateMenu' + messageId);
    if (menu) {
        menu.classList.remove('show');
    }
    
    // Appeler la fonction de traduction
    if (typeof translateMessage === 'function') {
        translateMessage(messageId, targetLang);
    } else {
        console.error('translateMessage non définie');
    }
    
    return false;
};

/**
 * Fermer une traduction
 */
window.closeTranslation = function(messageId) {
    console.log('closeTranslation appelée:', messageId);
    
    const container = document.getElementById('translated-text-' + messageId);
    if (container) {
        container.style.display = 'none';
        container.innerHTML = '';
    }
};

/**
 * Détection améliorée de la langue d'un texte
 */
function detectLanguage(text) {
    if (!text || text.length < 2) return 'en';
    
    const lowerText = text.toLowerCase();
    
    // Caractères arabes
    const arabicPattern = /[\u0600-\u06FF]/;
    if (arabicPattern.test(text)) {
        return 'ar';
    }
    
    // Caractères chinois
    const chinesePattern = /[\u4e00-\u9fff]/;
    if (chinesePattern.test(text)) {
        return 'zh';
    }
    
    // Caractères japonais
    const japanesePattern = /[\u3040-\u309f\u30a0-\u30ff]/;
    if (japanesePattern.test(text)) {
        return 'ja';
    }
    
    // Caractères cyrilliques (russe)
    const cyrillicPattern = /[\u0400-\u04FF]/;
    if (cyrillicPattern.test(text)) {
        return 'ru';
    }
    
    // Mots français très spécifiques (avec accents)
    const frenchSpecific = ['être', 'été', 'où', 'ça', 'français', 'très', 'déjà', 'après', 'même', 'voilà'];
    if (frenchSpecific.some(word => lowerText.includes(word))) {
        return 'fr';
    }
    
    // Mots français courants (étendu)
    const frenchWords = [
        // Articles
        'le', 'la', 'les', 'un', 'une', 'des', 'du', 'de', 'au', 'aux',
        // Pronoms
        'je', 'tu', 'il', 'elle', 'nous', 'vous', 'ils', 'elles', 'on',
        'me', 'te', 'se', 'lui', 'leur', 'moi', 'toi',
        // Verbes courants
        'est', 'sont', 'suis', 'es', 'être', 'avoir', 'ai', 'as', 'a', 'avons', 'avez', 'ont',
        'faire', 'fais', 'fait', 'faisons', 'faites', 'font',
        'dire', 'dis', 'dit', 'disons', 'dites', 'disent',
        'aller', 'vais', 'vas', 'va', 'allons', 'allez', 'vont',
        'voir', 'vois', 'voit', 'voyons', 'voyez', 'voient',
        'savoir', 'sais', 'sait', 'savons', 'savez', 'savent',
        'pouvoir', 'peux', 'peut', 'pouvons', 'pouvez', 'peuvent',
        'vouloir', 'veux', 'veut', 'voulons', 'voulez', 'veulent',
        // Mots courants
        'bonjour', 'salut', 'merci', 'oui', 'non', 'comment', 'pourquoi', 'quand', 'où', 'qui', 'que', 'quoi',
        'avec', 'sans', 'pour', 'dans', 'sur', 'sous', 'entre', 'chez', 'vers', 'par',
        'mais', 'ou', 'et', 'donc', 'or', 'ni', 'car',
        'bien', 'très', 'plus', 'moins', 'aussi', 'encore', 'déjà', 'toujours', 'jamais',
        'tout', 'tous', 'toute', 'toutes', 'quelque', 'chaque', 'plusieurs',
        'mon', 'ton', 'son', 'ma', 'ta', 'sa', 'mes', 'tes', 'ses', 'notre', 'votre', 'leur',
        'ce', 'cet', 'cette', 'ces',
        'quel', 'quelle', 'quels', 'quelles'
    ];
    
    // Mots anglais courants (étendu)
    const englishWords = [
        // Articles
        'the', 'a', 'an',
        // Pronoms
        'i', 'you', 'he', 'she', 'it', 'we', 'they',
        'me', 'him', 'her', 'us', 'them',
        'my', 'your', 'his', 'her', 'its', 'our', 'their',
        'mine', 'yours', 'hers', 'ours', 'theirs',
        // Verbes courants
        'is', 'are', 'am', 'was', 'were', 'be', 'been', 'being',
        'have', 'has', 'had', 'having',
        'do', 'does', 'did', 'doing', 'done',
        'will', 'would', 'should', 'could', 'can', 'may', 'might', 'must',
        'go', 'goes', 'went', 'going', 'gone',
        'get', 'gets', 'got', 'getting', 'gotten',
        'make', 'makes', 'made', 'making',
        'know', 'knows', 'knew', 'knowing', 'known',
        'think', 'thinks', 'thought', 'thinking',
        'take', 'takes', 'took', 'taking', 'taken',
        'see', 'sees', 'saw', 'seeing', 'seen',
        'come', 'comes', 'came', 'coming',
        'want', 'wants', 'wanted', 'wanting',
        'use', 'uses', 'used', 'using',
        'find', 'finds', 'found', 'finding',
        'give', 'gives', 'gave', 'giving', 'given',
        'tell', 'tells', 'told', 'telling',
        'work', 'works', 'worked', 'working',
        'call', 'calls', 'called', 'calling',
        'try', 'tries', 'tried', 'trying',
        'ask', 'asks', 'asked', 'asking',
        'need', 'needs', 'needed', 'needing',
        'feel', 'feels', 'felt', 'feeling',
        'become', 'becomes', 'became', 'becoming',
        'leave', 'leaves', 'left', 'leaving',
        'put', 'puts', 'putting',
        // Mots courants
        'hello', 'hi', 'hey', 'thank', 'thanks', 'yes', 'no', 'ok', 'okay',
        'how', 'why', 'when', 'where', 'who', 'what', 'which',
        'with', 'without', 'for', 'in', 'on', 'at', 'to', 'from', 'by', 'about',
        'but', 'or', 'and', 'so', 'if', 'than', 'because',
        'very', 'too', 'more', 'most', 'less', 'least', 'also', 'just', 'only',
        'all', 'some', 'any', 'each', 'every', 'both', 'few', 'many', 'much',
        'this', 'that', 'these', 'those',
        'not', 'no', 'yes'
    ];
    
    // Compter les mots français et anglais
    const words = lowerText.split(/\s+/);
    let frenchCount = 0;
    let englishCount = 0;
    let totalWords = 0;
    
    words.forEach(word => {
        // Nettoyer le mot (enlever ponctuation)
        const cleanWord = word.replace(/[.,!?;:'"()]/g, '');
        if (cleanWord.length < 2) return;
        
        totalWords++;
        
        if (frenchWords.includes(cleanWord)) {
            frenchCount++;
        }
        if (englishWords.includes(cleanWord)) {
            englishCount++;
        }
    });
    
    // Calculer les pourcentages
    const frenchPercent = totalWords > 0 ? (frenchCount / totalWords) * 100 : 0;
    const englishPercent = totalWords > 0 ? (englishCount / totalWords) * 100 : 0;
    
    console.log('📊 Détection de langue:', {
        text: text.substring(0, 50),
        totalWords,
        frenchCount,
        englishCount,
        frenchPercent: frenchPercent.toFixed(1) + '%',
        englishPercent: englishPercent.toFixed(1) + '%'
    });
    
    // Seuil de confiance : au moins 30% des mots doivent correspondre
    if (frenchPercent >= 30 && frenchPercent > englishPercent) {
        return 'fr';
    } else if (englishPercent >= 30 && englishPercent > frenchPercent) {
        return 'en';
    }
    
    // Si pas assez de confiance, utiliser des heuristiques supplémentaires
    
    // Vérifier les accents français
    const frenchAccents = /[àâäéèêëïîôùûüÿœæç]/i;
    if (frenchAccents.test(text)) {
        return 'fr';
    }
    
    // Vérifier les contractions anglaises
    const englishContractions = /'(s|t|re|ve|d|ll|m)\b/i;
    if (englishContractions.test(text)) {
        return 'en';
    }
    
    // Par défaut, considérer comme anglais
    return 'en';
}

/**
 * Traduire un message (appel AJAX) avec détection intelligente de langue
 */
window.translateMessage = async function(messageId, targetLang) {
    console.log('=== translateMessage appelée ===');
    console.log('messageId:', messageId);
    console.log('targetLang initial:', targetLang);
    
    // Fermer le menu de traduction s'il est ouvert
    const menu = document.getElementById('translateMenu' + messageId);
    if (menu) {
        menu.classList.remove('show');
    }
    
    const container = document.getElementById('translated-text-' + messageId);
    if (!container) {
        console.error('❌ Conteneur non trouvé:', 'translated-text-' + messageId);
        alert('Erreur: Conteneur de traduction non trouvé');
        return;
    }
    console.log('✅ Conteneur trouvé:', container);

    // Récupérer le texte du message pour détecter la langue
    const messageWrapper = document.querySelector(`[data-message-id="${messageId}"]`);
    console.log('Message wrapper:', messageWrapper);
    
    const messageBubble = messageWrapper ? messageWrapper.querySelector('.message-bubble') : null;
    console.log('Message bubble:', messageBubble);
    
    const messageText = messageBubble ? messageBubble.textContent.trim() : '';
    console.log('Texte du message:', messageText);
    
    // Détecter la langue du message
    const detectedLang = detectLanguage(messageText);
    console.log('🔍 Langue détectée:', detectedLang);
    
    // Si le message est déjà dans la langue cible, ne pas traduire
    if (detectedLang === targetLang) {
        console.log('⚠️ Message déjà en', targetLang);
        container.style.display = 'block';
        container.innerHTML =
            '<div class="translated-text-inner">' +
                '<span class="translation-flag">ℹ️</span>' +
                '<span class="translation-content">' +
                    '<strong class="translation-lang">Information</strong>' +
                    '<span class="translation-text">Ce message est déjà en ' + targetLang.toUpperCase() + '</span>' +
                '</span>' +
                '<button class="btn-close-translation" onclick="closeTranslation(' + messageId + ')" title="Fermer">' +
                    '<i class="fas fa-times"></i>' +
                '</button>' +
            '</div>';
        return;
    }

    if (!targetLang) {
        targetLang = 'en';
    }
    
    console.log('🎯 Langue cible finale:', targetLang);

    // Afficher le spinner
    container.style.display = 'block';
    container.innerHTML =
        '<div class="translated-text-inner">' +
            '<span class="spinner"><i class="fas fa-spinner fa-spin"></i></span> ' +
            '<span>Traduction en cours...</span>' +
        '</div>';
    console.log('⏳ Spinner affiché');

    try {
        const params = new URLSearchParams();
        params.append('lang', targetLang);
        
        const url = '/message/' + messageId + '/translate';
        console.log('📡 Appel API:', url, 'avec lang:', targetLang);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        });
        
        console.log('📥 Réponse reçue, status:', response.status);

        const contentType = response.headers.get('content-type') || '';
        console.log('Content-Type:', contentType);
        
        if (!contentType.includes('application/json')) {
            const txt = await response.text();
            console.error('❌ Réponse non JSON:', txt);
            container.innerHTML =
                '<div class="translated-text-inner">' +
                    '<i class="fas fa-exclamation-triangle"></i>' +
                    '<span>Erreur: Réponse non JSON</span>' +
                '</div>';
            return;
        }

        const data = await response.json();
        console.log('📦 Données JSON:', data);

        if (data.error) {
            console.error('❌ Erreur API:', data.error);
            container.innerHTML =
                '<div class="translated-text-inner">' +
                    '<i class="fas fa-exclamation-triangle"></i>' +
                    '<span>' + data.error + '</span>' +
                '</div>';
            return;
        }

        const langLabel = data.targetLanguage || targetLang.toUpperCase();
        const translation = data.translation || 'Traduction non disponible';
        const cached = data.cached ? ' 💾' : '';
        const provider = data.provider ? data.provider.toUpperCase() : '';
        
        // Emoji de drapeau selon la langue
        const flagEmoji = {
            'fr': '🇫🇷',
            'en': '🇬🇧',
            'ar': '🇸🇦',
            'es': '🇪🇸',
            'de': '🇩🇪',
            'it': '🇮🇹',
            'pt': '🇵🇹',
            'ru': '🇷🇺',
            'zh': '🇨🇳',
            'ja': '🇯🇵'
        };
        
        const flag = flagEmoji[targetLang] || '🌐';
        
        console.log('✅ Traduction reçue:', translation);
        console.log('📊 Cached:', data.cached, 'Provider:', data.provider);

        container.style.display = 'block';
        container.innerHTML =
            '<div class="translated-text-inner">' +
                '<span class="translation-flag">' + flag + '</span>' +
                '<span class="translation-content">' +
                    '<strong class="translation-lang">' + langLabel + cached + '</strong>' +
                    '<span class="translation-text">' + translation + '</span>' +
                '</span>' +
                '<button class="btn-close-translation" onclick="closeTranslation(' + messageId + ')" title="Fermer la traduction">' +
                    '<i class="fas fa-times"></i>' +
                '</button>' +
            '</div>';
            
        console.log('✅ Traduction affichée avec succès dans le DOM');
        console.log('Container display:', container.style.display);
        console.log('Container innerHTML:', container.innerHTML);
    } catch (e) {
        console.error('❌ Erreur translateMessage:', e);
        console.error('Stack:', e.stack);
        container.innerHTML =
            '<div class="translated-text-inner">' +
                '<i class="fas fa-exclamation-triangle"></i>' +
                '<span>Erreur: ' + e.message + '</span>' +
            '</div>';
    }
};

// Fermer les menus au clic extérieur
document.addEventListener('click', function(event) {
    if (!event.target.closest('.translate-wrapper')) {
        document.querySelectorAll('.translate-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', function() {
    console.log('Translation.js chargé');
    console.log('Fonctions disponibles:', {
        toggleTranslateMenu: typeof window.toggleTranslateMenu,
        translateMessageTo: typeof window.translateMessageTo,
        translateMessage: typeof window.translateMessage,
        closeTranslation: typeof window.closeTranslation
    });
});
