<?php

require_once __DIR__.'/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

$entityManager = $container->get('doctrine')->getManager();

// Supprimer tous les tokens de reset expirés ou anciens
$sql = "DELETE FROM reset_password_request WHERE expires_at < NOW() OR requested_at < NOW() - INTERVAL '1 day'";
$stmt = $entityManager->getConnection()->prepare($sql);
$stmt->executeStatement();

echo "✅ Tokens de reset nettoyés !\n";
echo "Vous pouvez maintenant réessayer le reset password.\n";
