<?php

namespace App\Service;

use App\Entity\Reclamation;
use App\Entity\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;

class ReclamationNotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private NotifierInterface $notifier,
        private string $senderEmail = 'noreply@buildify.com'
    ) {
    }

    /**
     * Send notification when a new reclamation is created
     */
    public function notifyNewReclamation(Reclamation $reclamation): void
    {
        // Send email to user confirming receipt
        $this->sendReclamationConfirmationEmail($reclamation);
        
        // Notify admin about new reclamation
        $this->notifyAdminNewReclamation($reclamation);
    }

    /**
     * Send notification when admin responds to a reclamation
     */
    public function notifyReclamationResponse(Reclamation $reclamation, Response $response): void
    {
        $user = $reclamation->getUser();
        
        // Send email to user
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($user->getEmail())
            ->subject('Réponse à votre réclamation')
            ->html($this->getResponseEmailTemplate($reclamation, $response));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            error_log('Failed to send reclamation response email: ' . $e->getMessage());
            throw $e; // Re-throw to see the actual error
        }

        // Send notification
        $notification = (new Notification(
            'Nouvelle réponse à votre réclamation',
            ['email']
        ))
            ->content('Notre équipe a répondu à votre réclamation.')
            ->importance(Notification::IMPORTANCE_HIGH);

        try {
            $recipient = new Recipient($user->getEmail());
            $this->notifier->send($notification, $recipient);
        } catch (\Exception $e) {
            error_log('Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Send confirmation email to user
     */
    private function sendReclamationConfirmationEmail(Reclamation $reclamation): void
    {
        $user = $reclamation->getUser();
        
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($user->getEmail())
            ->subject('Confirmation de votre réclamation')
            ->html($this->getConfirmationEmailTemplate($reclamation));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Failed to send confirmation email: ' . $e->getMessage());
        }
    }

    /**
     * Notify admin about new reclamation
     */
    private function notifyAdminNewReclamation(Reclamation $reclamation): void
    {
        $notification = (new Notification(
            'Nouvelle réclamation reçue',
            ['email']
        ))
            ->content(sprintf(
                'Une nouvelle réclamation de type "%s" a été soumise par %s %s.',
                $reclamation->getType()->value,
                $reclamation->getUser()->getFirstName(),
                $reclamation->getUser()->getLastName()
            ))
            ->importance(Notification::IMPORTANCE_HIGH);

        try {
            // Send to admin email (configure in notifier.yaml)
            $this->notifier->send($notification);
        } catch (\Exception $e) {
            error_log('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Get confirmation email template
     */
    private function getConfirmationEmailTemplate(Reclamation $reclamation): string
    {
        $user = $reclamation->getUser();
        
        return sprintf('
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #fbb6ce 0%%, #d8b4fe 100%%); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center; }
                    .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                    .badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: bold; }
                    .badge-info { background: #dbeafe; color: #1e40af; }
                    .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>✅ Réclamation reçue</h1>
                    </div>
                    <div class="content">
                        <p>Bonjour %s,</p>
                        <p>Nous avons bien reçu votre réclamation et nous vous remercions de nous avoir contactés.</p>
                        
                        <h3>Détails de votre réclamation :</h3>
                        <p><strong>Type :</strong> <span class="badge badge-info">%s</span></p>
                        <p><strong>Date :</strong> %s</p>
                        <p><strong>Contenu :</strong></p>
                        <p style="background: white; padding: 15px; border-left: 4px solid #d8b4fe; border-radius: 5px;">%s</p>
                        
                        <p>Notre équipe examine votre demande et vous répondra dans les plus brefs délais.</p>
                        
                        <p>Cordialement,<br><strong>L\'équipe Buildify</strong></p>
                    </div>
                    <div class="footer">
                        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
        ',
            $user->getFirstName(),
            $reclamation->getType()->value,
            $reclamation->getCreatedAt()->format('d/m/Y à H:i'),
            nl2br(htmlspecialchars($reclamation->getContent()))
        );
    }

    /**
     * Get response email template
     */
    private function getResponseEmailTemplate(Reclamation $reclamation, Response $response): string
    {
        $user = $reclamation->getUser();
        
        return sprintf('
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #fbb6ce 0%%, #d8b4fe 100%%); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center; }
                    .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                    .response-box { background: linear-gradient(135deg, #dbeafe 0%%, #e0e7ff 100%%); padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #3b82f6; }
                    .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>💬 Réponse à votre réclamation</h1>
                    </div>
                    <div class="content">
                        <p>Bonjour %s,</p>
                        <p>Notre équipe a examiné votre réclamation et vous apporte la réponse suivante :</p>
                        
                        <div class="response-box">
                            <p><strong>👤 Équipe Support</strong></p>
                            <p>%s</p>
                            <p style="color: #6b7280; font-size: 12px; margin-top: 10px;">%s</p>
                        </div>
                        
                        <p><strong>Votre réclamation initiale :</strong></p>
                        <p style="background: white; padding: 15px; border-radius: 5px; color: #6b7280;">%s</p>
                        
                        <p>Si vous avez d\'autres questions, n\'hésitez pas à nous contacter.</p>
                        
                        <p>Cordialement,<br><strong>L\'équipe Buildify</strong></p>
                    </div>
                    <div class="footer">
                        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
        ',
            $user->getFirstName(),
            nl2br(htmlspecialchars($response->getContent())),
            $response->getCreatedAt()->format('d/m/Y à H:i'),
            nl2br(htmlspecialchars($reclamation->getContent()))
        );
    }
}
