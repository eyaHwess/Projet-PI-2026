<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class MentionDetector
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * Detect @mentions in content and return mentioned users
     * 
     * @param string $content Message content
     * @return array Array of User entities
     */
    public function detectMentions(string $content): array
    {
        // Regex pour détecter @username (lettres, chiffres, underscore, tiret)
        preg_match_all('/@([a-zA-Z0-9_\-]+)/', $content, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $usernames = array_unique($matches[1]);
        $users = [];

        foreach ($usernames as $username) {
            // Chercher l'utilisateur par email (avant @)
            // ou par nom complet
            $user = $this->findUserByMention($username);
            if ($user) {
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * Find user by mention string
     * Tries to match by:
     * 1. Email prefix (before @)
     * 2. First name
     * 3. Last name
     * 4. Full name (firstname.lastname)
     */
    private function findUserByMention(string $mention): ?User
    {
        // Essayer de trouver par email
        $user = $this->userRepository->findOneBy(['email' => $mention . '@gmail.com']);
        if ($user) {
            return $user;
        }

        // Essayer de trouver par prénom
        $user = $this->userRepository->createQueryBuilder('u')
            ->where('LOWER(u.firstName) = LOWER(:mention)')
            ->setParameter('mention', $mention)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user) {
            return $user;
        }

        // Essayer de trouver par nom
        $user = $this->userRepository->createQueryBuilder('u')
            ->where('LOWER(u.lastName) = LOWER(:mention)')
            ->setParameter('mention', $mention)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user) {
            return $user;
        }

        // Essayer de trouver par nom complet (firstname.lastname ou firstname_lastname)
        $parts = preg_split('/[._\-]/', $mention);
        if (count($parts) === 2) {
            $user = $this->userRepository->createQueryBuilder('u')
                ->where('LOWER(u.firstName) = LOWER(:firstName)')
                ->andWhere('LOWER(u.lastName) = LOWER(:lastName)')
                ->setParameter('firstName', $parts[0])
                ->setParameter('lastName', $parts[1])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Replace @mentions with clickable links in HTML
     */
    public function replaceMentionsWithLinks(string $content): string
    {
        return preg_replace_callback(
            '/@([a-zA-Z0-9_\-]+)/',
            function ($matches) {
                $username = $matches[1];
                $user = $this->findUserByMention($username);
                
                if ($user) {
                    return sprintf(
                        '<span class="mention" data-user-id="%d">@%s</span>',
                        $user->getId(),
                        $username
                    );
                }
                
                return $matches[0];
            },
            $content
        );
    }

    /**
     * Get mention suggestions for autocomplete
     */
    public function getSuggestions(string $query, int $limit = 10): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $users = $this->userRepository->createQueryBuilder('u')
            ->where('LOWER(u.firstName) LIKE LOWER(:query)')
            ->orWhere('LOWER(u.lastName) LIKE LOWER(:query)')
            ->orWhere('LOWER(u.email) LIKE LOWER(:query)')
            ->setParameter('query', $query . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(function (User $user) {
            return [
                'id' => $user->getId(),
                'username' => strtolower($user->getFirstName()),
                'fullName' => $user->getFirstName() . ' ' . $user->getLastName(),
                'email' => $user->getEmail()
            ];
        }, $users);
    }
}
