<?php

namespace App\Service;

use App\Entity\CoachingRequest;
use InvalidArgumentException;

class CoachingRequestManager
{
    /**
     * Valide les règles métier d'une demande de coaching.
     *
     * @throws InvalidArgumentException Si une règle métier n'est pas respectée
     */
    public function validate(CoachingRequest $request): bool
    {
        // Règle 1 : Le message est obligatoire (non vide)
        $message = $request->getMessage();
        if ($message === null || trim($message) === '') {
            throw new InvalidArgumentException('Le message de la demande de coaching est obligatoire et ne doit pas être vide.');
        }

        // Règle 2 : Un coach doit être sélectionné
        if ($request->getCoach() === null) {
            throw new InvalidArgumentException('Un coach doit être sélectionné pour la demande de coaching.');
        }

        // Règle 3 : Le statut initial doit être "pending"
        if ($request->getStatus() !== CoachingRequest::STATUS_PENDING) {
            throw new InvalidArgumentException(
                sprintf('Le statut initial d\'une demande doit être "pending", "%s" fourni.', $request->getStatus())
            );
        }

        return true;
    }
}
