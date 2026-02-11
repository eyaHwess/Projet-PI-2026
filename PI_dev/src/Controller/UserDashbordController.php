<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

class UserDashbordController extends AbstractController
{
    #[Route('/user/dashboard', name: 'user_dashboard')]
public function dashboard(UserRepository $userRepository)
{
    $users = $userRepository->findAll();
    $usersCount = $userRepository->count([]);

    return $this->render('user_dashboard/index.html.twig', [
        'users' => $users,
        'usersCount' => $usersCount,
    ]);
}
    
}

