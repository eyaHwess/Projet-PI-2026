<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard/dashboard.html.twig');
    }
     #[Route('/admin/users', name: 'admin_user_list')]
    public function userList(): Response
    {
        return $this->render('admin/user_list.html.twig');
    }
    #[Route('/admin/manageUsers', name: 'admin_manage_accounts')]
    public function manageAccounts(): Response
    {
        return $this->render('admin/manage_accounts.html.twig');
    }
    #[Route('/admin/userDetail', name: 'admin_user_detail')]
    public function detail(): Response
    {
        // Static example user (for now)
        return $this->render('admin/user_detail.html.twig');
    }
}
