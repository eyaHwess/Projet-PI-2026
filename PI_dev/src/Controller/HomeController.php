<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Navbar DayFlow (landing) uniquement pour les visiteurs non connectés
        if ($this->getUser()) {
            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('homepage/index.html.twig');
    }
}
