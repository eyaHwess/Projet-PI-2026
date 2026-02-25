<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/test')]
class TestNotificationController extends AbstractController
{
    #[Route('/notifications', name: 'test_notifications')]
    public function notifications(): Response
    {
        return $this->render('test/notifications.html.twig');
    }
}
