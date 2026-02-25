<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Service\ModerationService;
use Psr\Log\NullLogger;

$service = new ModerationService(new NullLogger());

$message = "you are a fucking asshole";
$result = $service->analyzeMessage($message);

echo "Message: \"{$message}\"\n";
echo "Score toxicité: " . $result['toxicityScore'] . "\n";
echo "Seuil: 0.6\n";
echo "Est toxique: " . ($result['isToxic'] ? 'OUI' : 'NON') . "\n";
echo "Statut: " . $result['moderationStatus'] . "\n";
echo "Raison: " . ($result['moderationReason'] ?? 'Aucune') . "\n";
echo "\nMots détectés: " . implode(', ', $result['details']['toxicWords']) . "\n";
