<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestNotificationController extends AbstractController
{
    #[Route('/test-notification', name: 'test_notification')]
    public function index(): Response
    {
        return new Response('Test notification works');
    }
}
