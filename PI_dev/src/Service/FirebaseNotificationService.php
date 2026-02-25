<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Message;
use App\Entity\Goal;
use App\Repository\FcmTokenRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FirebaseNotificationService
{
    private const FCM_URL = 'https://fcm.googleapis.com/fcm/send';

    public function __construct(
        private HttpClientInterface $httpClient,
        private FcmTokenRepository $fcmTokenRepository,
        private LoggerInterface $logger,
        private string $firebaseServerKey
    ) {
    }

    /**
     * Send notification to a single user
     */
    public function sendToUser(User $user, array $notification, array $data = []): bool
    {
        $tokens = $this->fcmTokenRepository->findByUser($user);
        
        if (empty($tokens)) {
            $this->logger->info('No FCM tokens found for user', ['userId' => $user->getId()]);
            return false;
        }

        $success = true;
        foreach ($tokens as $fcmToken) {
            if (!$this->sendToToken($fcmToken->getToken(), $notification, $data)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Send notification to multiple users
     */
    public function sendToMultipleUsers(array $users, array $notification, array $data = []): int
    {
        $tokens = $this->fcmTokenRepository->findByUsers($users);
        
        if (empty($tokens)) {
            return 0;
        }

        $successCount = 0;
        foreach ($tokens as $fcmToken) {
            if ($this->sendToToken($fcmToken->getToken(), $notification, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Send notification for new message
     */
    public function notifyNewMessage(Message $message): void
    {
        $chatroom = $message->getChatroom();
        if (!$chatroom) {
            return;
        }

        $goal = $chatroom->getGoal();
        $author = $message->getAuthor();
        
        // Get all members except the author
        $members = [];
        foreach ($goal->getGoalParticipations() as $participation) {
            if ($participation->getUser()->getId() !== $author->getId()) {
                $members[] = $participation->getUser();
            }
        }

        if (empty($members)) {
            return;
        }

        $notification = [
            'title' => 'Nouveau message de ' . $author->getFirstName(),
            'body' => $this->truncateText($message->getContent() ?? '[Fichier joint]', 100),
            'icon' => '/images/logo.png',
            'badge' => '/images/badge.png',
            'tag' => 'message-' . $message->getId(),
        ];

        $data = [
            'type' => 'new_message',
            'messageId' => (string) $message->getId(),
            'chatroomId' => (string) $chatroom->getId(),
            'goalId' => (string) $goal->getId(),
            'authorId' => (string) $author->getId(),
            'authorName' => $author->getFirstName() . ' ' . $author->getLastName(),
            'url' => '/chatroom/' . $chatroom->getId(),
        ];

        $this->sendToMultipleUsers($members, $notification, $data);
    }

    /**
     * Send notification for new member
     */
    public function notifyNewMember(Goal $goal, User $newMember): void
    {
        // Get all existing members except the new one
        $members = [];
        foreach ($goal->getGoalParticipations() as $participation) {
            if ($participation->getUser()->getId() !== $newMember->getId()) {
                $members[] = $participation->getUser();
            }
        }

        if (empty($members)) {
            return;
        }

        $notification = [
            'title' => 'Nouveau membre dans "' . $goal->getTitle() . '"',
            'body' => $newMember->getFirstName() . ' ' . $newMember->getLastName() . ' a rejoint le goal',
            'icon' => '/images/logo.png',
        ];

        $data = [
            'type' => 'new_member',
            'goalId' => (string) $goal->getId(),
            'memberId' => (string) $newMember->getId(),
            'memberName' => $newMember->getFirstName() . ' ' . $newMember->getLastName(),
            'url' => '/goal/' . $goal->getId(),
        ];

        $this->sendToMultipleUsers($members, $notification, $data);
    }

    /**
     * Send notification for mention
     */
    public function notifyMention(Message $message, User $mentionedUser): void
    {
        $author = $message->getAuthor();
        
        // Don't notify if user mentions themselves
        if ($author->getId() === $mentionedUser->getId()) {
            return;
        }

        $chatroom = $message->getChatroom();
        
        $notification = [
            'title' => $author->getFirstName() . ' vous a mentionné',
            'body' => $this->truncateText($message->getContent() ?? '', 100),
            'icon' => '/images/logo.png',
            'tag' => 'mention-' . $message->getId(),
            'requireInteraction' => true,
        ];

        $data = [
            'type' => 'mention',
            'messageId' => (string) $message->getId(),
            'chatroomId' => (string) $chatroom->getId(),
            'authorId' => (string) $author->getId(),
            'authorName' => $author->getFirstName(),
            'url' => '/chatroom/' . $chatroom->getId() . '#message-' . $message->getId(),
        ];

        $this->sendToUser($mentionedUser, $notification, $data);
    }

    /**
     * Send notification to a specific token
     */
    private function sendToToken(string $token, array $notification, array $data = []): bool
    {
        try {
            $payload = [
                'to' => $token,
                'notification' => $notification,
                'data' => $data,
                'priority' => 'high',
            ];

            $response = $this->httpClient->request('POST', self::FCM_URL, [
                'headers' => [
                    'Authorization' => 'key=' . $this->firebaseServerKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            
            if ($statusCode === 200) {
                $content = $response->toArray();
                
                if (isset($content['failure']) && $content['failure'] > 0) {
                    $this->logger->warning('FCM notification failed', [
                        'token' => substr($token, 0, 20) . '...',
                        'response' => $content
                    ]);
                    
                    // Remove invalid token
                    if (isset($content['results'][0]['error']) && 
                        in_array($content['results'][0]['error'], ['InvalidRegistration', 'NotRegistered'])) {
                        $this->fcmTokenRepository->removeInvalidTokens([$token]);
                    }
                    
                    return false;
                }
                
                return true;
            }

            $this->logger->error('FCM request failed', [
                'statusCode' => $statusCode,
                'token' => substr($token, 0, 20) . '...'
            ]);

            return false;

        } catch (\Exception $e) {
            $this->logger->error('FCM notification error', [
                'message' => $e->getMessage(),
                'token' => substr($token, 0, 20) . '...'
            ]);

            return false;
        }
    }

    /**
     * Truncate text to specified length
     */
    private function truncateText(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }
}
