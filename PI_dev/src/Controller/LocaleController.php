<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController extends AbstractController
{
    private const SUPPORTED_LOCALES = ['en', 'fr', 'ar'];

    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/locale/{locale}', name: 'app_locale_switch', requirements: ['locale' => 'en|fr|ar'])]
    public function switchLocale(string $locale, Request $request): Response
    {
        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = 'en';
        }

        $session = $request->getSession();
        $session->set('_locale', $locale);

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $user->setPreferredLanguage($locale);
            $this->entityManager->flush();
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_home');
    }
}
