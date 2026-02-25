<?php

namespace App\NotificationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * NotificationBundle - Bundle de gestion des notifications
 * 
 * Ce bundle fournit un système complet de notifications pour les applications Symfony.
 * Il permet d'envoyer, stocker et gérer des notifications pour les utilisateurs.
 */
class NotificationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__).'/NotificationBundle';
    }
}
