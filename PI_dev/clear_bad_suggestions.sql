-- Clear all suggestions that contain error messages
DELETE FROM suggestion 
WHERE content LIKE '%Erreur inattendue%' 
   OR content LIKE '%HTTP/2 429%'
   OR content LIKE '%Coaching Temporairement Indisponible%'
   OR content LIKE '%Erreur d''authentification%'
   OR content LIKE '%Erreur de connexion%';

-- Verify remaining suggestions
SELECT id, user_id, created_at, LEFT(content, 100) as preview 
FROM suggestion 
ORDER BY created_at DESC;
