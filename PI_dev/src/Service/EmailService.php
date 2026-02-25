<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $fromEmail = 'noreply@dayflow.com',
        private string $fromName = 'DayFlow'
    ) {
    }

    public function sendRegistrationConfirmation(string $to, string $firstName): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($to)
            ->subject('Bienvenue sur DayFlow !')
            ->htmlTemplate('emails/registration_confirmation.html.twig')
            ->context([
                'firstName' => $firstName,
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->info('Email de confirmation envoyé', ['to' => $to]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur envoi email confirmation', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sendPasswordChanged(string $to, string $firstName): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($to)
            ->subject('Votre mot de passe a été modifié')
            ->htmlTemplate('emails/password_changed.html.twig')
            ->context([
                'firstName' => $firstName,
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->info('Email changement mot de passe envoyé', ['to' => $to]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur envoi email changement mot de passe', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sendSuspiciousLogin(
        string $to,
        string $firstName,
        string $ipAddress,
        string $userAgent,
        \DateTimeImmutable $loginTime
    ): void {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($to)
            ->subject('⚠️ Connexion suspecte détectée')
            ->htmlTemplate('emails/suspicious_login.html.twig')
            ->context([
                'firstName' => $firstName,
                'ipAddress' => $ipAddress,
                'userAgent' => $userAgent,
                'loginTime' => $loginTime,
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->warning('Email connexion suspecte envoyé', [
                'to' => $to,
                'ip' => $ipAddress
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur envoi email connexion suspecte', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sendPasswordResetLink(string $to, string $firstName, string $resetToken): void
    {
        $this->logger->info('🔍 DEBUG: sendPasswordResetLink appelée', [
            'to' => $to,
            'firstName' => $firstName,
            'token_length' => strlen($resetToken)
        ]);

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($to)
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('emails/reset_password.html.twig')
            ->context([
                'firstName' => $firstName,
                'resetToken' => $resetToken,
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->info('✅ Email reset password envoyé', ['to' => $to]);
        } catch (\Exception $e) {
            $this->logger->error('❌ Erreur envoi email reset password', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-throw pour que le contrôleur puisse gérer
        }
    }

    public function sendRoutineReminder(string $to, string $firstName, array $routines): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($to)
            ->subject('📅 Rappel de vos routines du jour')
            ->htmlTemplate('emails/routine_reminder.html.twig')
            ->context([
                'firstName' => $firstName,
                'routines' => $routines,
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->info('Email rappel routine envoyé', ['to' => $to]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur envoi email rappel routine', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
        }
    }
}
