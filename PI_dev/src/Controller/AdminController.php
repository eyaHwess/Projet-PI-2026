<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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
     #[Route('/admin/coaches', name: 'coaches_list')]
    public function coachList(): Response
    {
        return $this->render('admin/coaches_list.html.twig');
    }
    #[Route('/admin/manageUsers', name: 'admin_manage_accounts')]
    public function manageAccounts(Request $request): Response
    {
        // Get current page from query parameter, default to 1
        $currentPage = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = 4;
        
        // All static users data
        $allUsers = [
            [
                'name' => 'Lori Stevens',
                'goal' => 'Model body in 15 days',
                'routines' => 15,
                'streakDays' => '0',
                'avatar' => '/adminDashboard/assets/images/avatar/09.jpg'
            ],
            [
                'name' => 'Carolyn Ortiz',
                'goal' => 'discipline',
                'routines' => 10,
                'streakDays' => '23',
                'avatar' => '/adminDashboard/assets/images/avatar/01.jpg'
            ],
            [
                'name' => 'Dennis Barrett',
                'goal' => 'health',
                'routines' => 9,
                'streakDays' => '246',
                'avatar' => '/adminDashboard/assets/images/avatar/04.jpg'
            ],
            [
                'name' => 'Amanda Reed',
                'goal' => 'great diet',
                'routines' => 29,
                'streakDays' => '46',
                'avatar' => '/adminDashboard/assets/images/avatar/05.jpg'
            ],
            [
                'name' => 'Michael Johnson',
                'goal' => 'fitness journey',
                'routines' => 12,
                'streakDays' => '89',
                'avatar' => '/adminDashboard/assets/images/avatar/06.jpg'
            ],
            [
                'name' => 'Sarah Williams',
                'goal' => 'weight loss',
                'routines' => 18,
                'streakDays' => '134',
                'avatar' => '/adminDashboard/assets/images/avatar/07.jpg'
            ],
            [
                'name' => 'David Brown',
                'goal' => 'muscle gain',
                'routines' => 22,
                'streakDays' => '67',
                'avatar' => '/adminDashboard/assets/images/avatar/08.jpg'
            ],
            [
                'name' => 'Emily Davis',
                'goal' => 'healthy lifestyle',
                'routines' => 14,
                'streakDays' => '178',
                'avatar' => '/adminDashboard/assets/images/avatar/02.jpg'
            ],
            [
                'name' => 'James Wilson',
                'goal' => 'strength training',
                'routines' => 20,
                'streakDays' => '92',
                'avatar' => '/adminDashboard/assets/images/avatar/03.jpg'
            ],
            [
                'name' => 'Jessica Martinez',
                'goal' => 'cardio improvement',
                'routines' => 16,
                'streakDays' => '45',
                'avatar' => '/adminDashboard/assets/images/avatar/10.jpg'
            ],
            [
                'name' => 'Robert Taylor',
                'goal' => 'flexibility',
                'routines' => 11,
                'streakDays' => '156',
                'avatar' => '/adminDashboard/assets/images/avatar/11.jpg'
            ],
            [
                'name' => 'Linda Anderson',
                'goal' => 'stress relief',
                'routines' => 13,
                'streakDays' => '201',
                'avatar' => '/adminDashboard/assets/images/avatar/12.jpg'
            ],
        ];
        
        // Calculate pagination
        $totalItems = count($allUsers);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        // Get items for current page
        $users = array_slice($allUsers, $offset, $itemsPerPage);
        
        return $this->render('admin/manage_accounts.html.twig', [
            'users' => $users,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage,
            'startItem' => $offset + 1,
            'endItem' => min($offset + $itemsPerPage, $totalItems)
        ]);
    }
    #[Route('/admin/userDetail', name: 'admin_user_detail')]
    public function detail(): Response
    {
        // Static example user (for now)
        return $this->render('admin/user_detail.html.twig');
    }

    #[Route('/admin/coach/requests', name: 'admin_coach_requests')]
    public function coachRequests(Request $request): Response
    {
        // Get current page from query parameter, default to 1
        $currentPage = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = 4;
        
        // All static data for testing
        $allRequests = [
            [
                'id' => 1,
                'user' => [
                    'name' => 'Lori Stevens',
                    'avatar' => '/adminDashboard/assets/images/avatar/01.jpg'
                ],
                'coach' => [
                    'name' => 'John Smith',
                    'specialty' => 'Nutrition & Diet',
                    'avatar' => '/adminDashboard/assets/images/avatar/07.jpg'
                ],
                'createdAt' => new \DateTime('2021-10-22'),
                'status' => 'pending'
            ],
            [
                'id' => 2,
                'user' => [
                    'name' => 'Carolyn Ortiz',
                    'avatar' => '/adminDashboard/assets/images/avatar/02.jpg'
                ],
                'coach' => [
                    'name' => 'Sarah Johnson',
                    'specialty' => 'Study & Productivity',
                    'avatar' => '/adminDashboard/assets/images/avatar/08.jpg'
                ],
                'createdAt' => new \DateTime('2021-09-06'),
                'status' => 'pending'
            ],
            [
                'id' => 3,
                'user' => [
                    'name' => 'Dennis Barrett',
                    'avatar' => '/adminDashboard/assets/images/avatar/03.jpg'
                ],
                'coach' => [
                    'name' => 'Mike Wilson',
                    'specialty' => 'Time Management',
                    'avatar' => '/adminDashboard/assets/images/avatar/09.jpg'
                ],
                'createdAt' => new \DateTime('2021-01-21'),
                'status' => 'accepted'
            ],
            [
                'id' => 4,
                'user' => [
                    'name' => 'Billy Vasquez',
                    'avatar' => '/adminDashboard/assets/images/avatar/04.jpg'
                ],
                'coach' => [
                    'name' => 'Emma Davis',
                    'specialty' => 'Fitness & Wellness',
                    'avatar' => '/adminDashboard/assets/images/avatar/10.jpg'
                ],
                'createdAt' => new \DateTime('2020-12-25'),
                'status' => 'rejected'
            ],
            [
                'id' => 5,
                'user' => [
                    'name' => 'Jacqueline Miller',
                    'avatar' => '/adminDashboard/assets/images/avatar/05.jpg'
                ],
                'coach' => [
                    'name' => 'David Brown',
                    'specialty' => 'Mental Health & Mindfulness',
                    'avatar' => '/adminDashboard/assets/images/avatar/11.jpg'
                ],
                'createdAt' => new \DateTime('2020-06-05'),
                'status' => 'accepted'
            ],
            [
                'id' => 6,
                'user' => [
                    'name' => 'Amanda Reed',
                    'avatar' => '/adminDashboard/assets/images/avatar/06.jpg'
                ],
                'coach' => [
                    'name' => 'Lisa Anderson',
                    'specialty' => 'Career & Goal Setting',
                    'avatar' => '/adminDashboard/assets/images/avatar/12.jpg'
                ],
                'createdAt' => new \DateTime('2020-02-14'),
                'status' => 'accepted'
            ],
            [
                'id' => 7,
                'user' => [
                    'name' => 'Robert Taylor',
                    'avatar' => '/adminDashboard/assets/images/avatar/09.jpg'
                ],
                'coach' => [
                    'name' => 'Jennifer White',
                    'specialty' => 'Sleep & Recovery',
                    'avatar' => '/adminDashboard/assets/images/avatar/01.jpg'
                ],
                'createdAt' => new \DateTime('2020-01-10'),
                'status' => 'pending'
            ],
            [
                'id' => 8,
                'user' => [
                    'name' => 'Maria Garcia',
                    'avatar' => '/adminDashboard/assets/images/avatar/10.jpg'
                ],
                'coach' => [
                    'name' => 'Thomas Moore',
                    'specialty' => 'Habit Building',
                    'avatar' => '/adminDashboard/assets/images/avatar/02.jpg'
                ],
                'createdAt' => new \DateTime('2019-12-15'),
                'status' => 'accepted'
            ],
            [
                'id' => 9,
                'user' => [
                    'name' => 'James Wilson',
                    'avatar' => '/adminDashboard/assets/images/avatar/11.jpg'
                ],
                'coach' => [
                    'name' => 'Patricia Martinez',
                    'specialty' => 'Work-Life Balance',
                    'avatar' => '/adminDashboard/assets/images/avatar/03.jpg'
                ],
                'createdAt' => new \DateTime('2019-11-20'),
                'status' => 'pending'
            ],
            [
                'id' => 10,
                'user' => [
                    'name' => 'Linda Anderson',
                    'avatar' => '/adminDashboard/assets/images/avatar/12.jpg'
                ],
                'coach' => [
                    'name' => 'Michael Jackson',
                    'specialty' => 'Stress Management',
                    'avatar' => '/adminDashboard/assets/images/avatar/04.jpg'
                ],
                'createdAt' => new \DateTime('2019-10-05'),
                'status' => 'rejected'
            ],
            [
                'id' => 11,
                'user' => [
                    'name' => 'Barbara Thomas',
                    'avatar' => '/adminDashboard/assets/images/avatar/01.jpg'
                ],
                'coach' => [
                    'name' => 'Christopher Lee',
                    'specialty' => 'Morning Routines',
                    'avatar' => '/adminDashboard/assets/images/avatar/05.jpg'
                ],
                'createdAt' => new \DateTime('2019-09-12'),
                'status' => 'accepted'
            ],
            [
                'id' => 12,
                'user' => [
                    'name' => 'Susan Harris',
                    'avatar' => '/adminDashboard/assets/images/avatar/02.jpg'
                ],
                'coach' => [
                    'name' => 'Daniel Clark',
                    'specialty' => 'Evening Routines',
                    'avatar' => '/adminDashboard/assets/images/avatar/06.jpg'
                ],
                'createdAt' => new \DateTime('2019-08-25'),
                'status' => 'pending'
            ],
            [
                'id' => 13,
                'user' => [
                    'name' => 'Jessica Lewis',
                    'avatar' => '/adminDashboard/assets/images/avatar/03.jpg'
                ],
                'coach' => [
                    'name' => 'Matthew Walker',
                    'specialty' => 'Meditation & Yoga',
                    'avatar' => '/adminDashboard/assets/images/avatar/07.jpg'
                ],
                'createdAt' => new \DateTime('2019-07-18'),
                'status' => 'accepted'
            ],
            [
                'id' => 14,
                'user' => [
                    'name' => 'Sarah Robinson',
                    'avatar' => '/adminDashboard/assets/images/avatar/04.jpg'
                ],
                'coach' => [
                    'name' => 'Anthony Hall',
                    'specialty' => 'Personal Development',
                    'avatar' => '/adminDashboard/assets/images/avatar/08.jpg'
                ],
                'createdAt' => new \DateTime('2019-06-30'),
                'status' => 'rejected'
            ],
            [
                'id' => 15,
                'user' => [
                    'name' => 'Karen Young',
                    'avatar' => '/adminDashboard/assets/images/avatar/05.jpg'
                ],
                'coach' => [
                    'name' => 'Mark Allen',
                    'specialty' => 'Financial Planning',
                    'avatar' => '/adminDashboard/assets/images/avatar/09.jpg'
                ],
                'createdAt' => new \DateTime('2019-05-22'),
                'status' => 'pending'
            ],
            [
                'id' => 16,
                'user' => [
                    'name' => 'Nancy King',
                    'avatar' => '/adminDashboard/assets/images/avatar/06.jpg'
                ],
                'coach' => [
                    'name' => 'Steven Wright',
                    'specialty' => 'Reading & Learning',
                    'avatar' => '/adminDashboard/assets/images/avatar/10.jpg'
                ],
                'createdAt' => new \DateTime('2019-04-14'),
                'status' => 'accepted'
            ],
            [
                'id' => 17,
                'user' => [
                    'name' => 'Betty Scott',
                    'avatar' => '/adminDashboard/assets/images/avatar/07.jpg'
                ],
                'coach' => [
                    'name' => 'Paul Lopez',
                    'specialty' => 'Exercise & Movement',
                    'avatar' => '/adminDashboard/assets/images/avatar/11.jpg'
                ],
                'createdAt' => new \DateTime('2019-03-08'),
                'status' => 'pending'
            ],
            [
                'id' => 18,
                'user' => [
                    'name' => 'Helen Green',
                    'avatar' => '/adminDashboard/assets/images/avatar/08.jpg'
                ],
                'coach' => [
                    'name' => 'Andrew Hill',
                    'specialty' => 'Journaling & Reflection',
                    'avatar' => '/adminDashboard/assets/images/avatar/12.jpg'
                ],
                'createdAt' => new \DateTime('2019-02-19'),
                'status' => 'accepted'
            ],
            [
                'id' => 19,
                'user' => [
                    'name' => 'Dorothy Adams',
                    'avatar' => '/adminDashboard/assets/images/avatar/09.jpg'
                ],
                'coach' => [
                    'name' => 'Joshua Baker',
                    'specialty' => 'Social Connection',
                    'avatar' => '/adminDashboard/assets/images/avatar/01.jpg'
                ],
                'createdAt' => new \DateTime('2019-01-11'),
                'status' => 'rejected'
            ],
            [
                'id' => 20,
                'user' => [
                    'name' => 'Sandra Nelson',
                    'avatar' => '/adminDashboard/assets/images/avatar/10.jpg'
                ],
                'coach' => [
                    'name' => 'Ryan Carter',
                    'specialty' => 'Creative Pursuits',
                    'avatar' => '/adminDashboard/assets/images/avatar/02.jpg'
                ],
                'createdAt' => new \DateTime('2018-12-28'),
                'status' => 'pending'
            ],
        ];
        
        // Calculate pagination
        $totalItems = count($allRequests);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        // Get items for current page
        $coachRequests = array_slice($allRequests, $offset, $itemsPerPage);
        
        return $this->render('admin/components/Coach/coachRequests.html.twig', [
            'coachRequests' => $coachRequests,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage,
            'startItem' => $offset + 1,
            'endItem' => min($offset + $itemsPerPage, $totalItems)
        ]);
    }

    #[Route('/admin/coach/request/{id}', name: 'admin_coach_request_view')]
    public function viewCoachRequest(int $id): Response
    {
        // Placeholder for viewing individual request
        return new Response('View request #' . $id);
    }

    #[Route('/admin/claims', name: 'admin_claims')]
    public function claims(Request $request): Response
    {
        // Get current page from query parameter, default to 1
        $currentPage = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = 4;
        
        // All static claims data for testing
        $allClaims = [
            [
                'id' => 1,
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/01.jpg',
                    'phoneNumber' => '+1 234 567 8901',
                    'age' => 28,
                    'status' => 'active'
                ],
                'content' => 'I have been experiencing issues with my routine tracking. The app crashes every time I try to mark a task as complete. This has been happening for the past 3 days and it\'s affecting my productivity.',
                'createdAt' => new \DateTime('2024-02-10 14:30:00')
            ],
            [
                'id' => 2,
                'user' => [
                    'name' => 'Sarah Williams',
                    'email' => 'sarah.williams@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/02.jpg',
                    'phoneNumber' => '+1 234 567 8902',
                    'age' => 32,
                    'status' => 'active'
                ],
                'content' => 'My coach hasn\'t responded to my messages for over a week. I need guidance on my nutrition routine but I can\'t get any support. Please help me resolve this issue.',
                'createdAt' => new \DateTime('2024-02-09 10:15:00')
            ],
            [
                'id' => 3,
                'user' => [
                    'name' => 'Michael Brown',
                    'email' => 'michael.brown@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/03.jpg',
                    'phoneNumber' => '+1 234 567 8903',
                    'age' => 25,
                    'status' => 'active'
                ],
                'content' => 'The payment system charged me twice for my monthly subscription. I have contacted support but haven\'t received a refund yet. My bank statement shows two transactions on the same day.',
                'createdAt' => new \DateTime('2024-02-08 16:45:00')
            ],
            [
                'id' => 4,
                'user' => [
                    'name' => 'Emily Davis',
                    'email' => 'emily.davis@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/04.jpg',
                    'phoneNumber' => '+1 234 567 8904',
                    'age' => 30,
                    'status' => 'active'
                ],
                'content' => 'I cannot access my workout routines. Every time I try to open the fitness section, I get an error message saying "Content not available". This is very frustrating.',
                'createdAt' => new \DateTime('2024-02-07 09:20:00')
            ],
            [
                'id' => 5,
                'user' => [
                    'name' => 'David Wilson',
                    'email' => 'david.wilson@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/05.jpg',
                    'phoneNumber' => '+1 234 567 8905',
                    'age' => 35,
                    'status' => 'active'
                ],
                'content' => 'My progress data has been lost after the last app update. All my streak days and completed routines are showing as zero. I had over 200 days of streak!',
                'createdAt' => new \DateTime('2024-02-06 13:00:00')
            ],
            [
                'id' => 6,
                'user' => [
                    'name' => 'Jessica Martinez',
                    'email' => 'jessica.martinez@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/06.jpg',
                    'phoneNumber' => '+1 234 567 8906',
                    'age' => 27,
                    'status' => 'active'
                ],
                'content' => 'The notification system is not working properly. I\'m not receiving reminders for my scheduled routines, which defeats the purpose of having a routine management app.',
                'createdAt' => new \DateTime('2024-02-05 11:30:00')
            ],
            [
                'id' => 7,
                'user' => [
                    'name' => 'Robert Taylor',
                    'email' => 'robert.taylor@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/07.jpg',
                    'phoneNumber' => '+1 234 567 8907',
                    'age' => 40,
                    'status' => 'active'
                ],
                'content' => 'I requested a coach change two weeks ago but my request is still pending. I need a coach who specializes in time management, not fitness.',
                'createdAt' => new \DateTime('2024-02-04 15:45:00')
            ],
            [
                'id' => 8,
                'user' => [
                    'name' => 'Amanda Garcia',
                    'email' => 'amanda.garcia@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/08.jpg',
                    'phoneNumber' => '+1 234 567 8908',
                    'age' => 29,
                    'status' => 'active'
                ],
                'content' => 'The app interface is very confusing. I can\'t find where to add new routines or edit existing ones. Better user guidance is needed.',
                'createdAt' => new \DateTime('2024-02-03 08:15:00')
            ],
            [
                'id' => 9,
                'user' => [
                    'name' => 'Christopher Lee',
                    'email' => 'christopher.lee@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/09.jpg',
                    'phoneNumber' => '+1 234 567 8909',
                    'age' => 33,
                    'status' => 'inactive'
                ],
                'content' => 'My account was suspended without any explanation. I haven\'t violated any terms of service. Please review my case and reactivate my account.',
                'createdAt' => new \DateTime('2024-02-02 12:00:00')
            ],
            [
                'id' => 10,
                'user' => [
                    'name' => 'Lisa Anderson',
                    'email' => 'lisa.anderson@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/10.jpg',
                    'phoneNumber' => '+1 234 567 8910',
                    'age' => 26,
                    'status' => 'active'
                ],
                'content' => 'The sync feature between mobile and web is not working. Changes I make on my phone don\'t appear on the web version and vice versa.',
                'createdAt' => new \DateTime('2024-02-01 17:30:00')
            ],
            [
                'id' => 11,
                'user' => [
                    'name' => 'Daniel White',
                    'email' => 'daniel.white@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/11.jpg',
                    'phoneNumber' => '+1 234 567 8911',
                    'age' => 31,
                    'status' => 'active'
                ],
                'content' => 'I\'m having trouble canceling my subscription. The cancel button doesn\'t work and I keep getting charged monthly.',
                'createdAt' => new \DateTime('2024-01-31 14:20:00')
            ],
            [
                'id' => 12,
                'user' => [
                    'name' => 'Michelle Harris',
                    'email' => 'michelle.harris@example.com',
                    'avatar' => '/adminDashboard/assets/images/avatar/12.jpg',
                    'phoneNumber' => '+1 234 567 8912',
                    'age' => 34,
                    'status' => 'active'
                ],
                'content' => 'The meditation timer feature is broken. It stops randomly in the middle of sessions and doesn\'t save my progress.',
                'createdAt' => new \DateTime('2024-01-30 10:45:00')
            ],
        ];
        
        // Calculate pagination
        $totalItems = count($allClaims);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        // Get items for current page
        $claims = array_slice($allClaims, $offset, $itemsPerPage);
        
        return $this->render('admin/components/reclamation/claims.html.twig', [
            'claims' => $claims,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage,
            'startItem' => $offset + 1,
            'endItem' => min($offset + $itemsPerPage, $totalItems)
        ]);
    }


    #[Route('/admin/statistics/users', name: 'admin_user_stats')]
    public function userStats(): Response
    {
        // Sample data for statistics
        $data = [
            // User Growth Card
            'totalUsers' => 1247,
            'weekGrowth' => 13,
            'monthGrowth' => 28,
            'newUsersWeek' => 156,
            
            // Active Routines Card
            'totalRoutines' => 3842,
            'routinesByGoal' => [
                'fitness' => 1523,
                'education' => 892,
                'productivity' => 745,
                'wellness' => 682
            ],
            
            // Completion Rate Card
            'completionRate' => 76,
            'dailyCompleted' => 2847,
            'activeStreaks' => 892,
            
            // User Engagement Chart Data
            'engagementData' => [
                'dates' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'activeUsers' => [820, 932, 901, 934, 1290, 1330, 1320],
                'completions' => [650, 780, 720, 810, 950, 1020, 980],
                'streaks' => [520, 580, 590, 620, 680, 720, 750]
            ],
            
            // Coach Performance Chart Data
            'coachData' => [
                'coaches' => ['John Smith', 'Sarah Johnson', 'Mike Wilson', 'Emma Davis', 'David Brown'],
                'sessions' => [45, 38, 52, 41, 36],
                'ratings' => [4.8, 4.9, 4.7, 4.6, 4.8]
            ],
            
            // Popular Routines Chart Data
            'routinesData' => [
                'categories' => ['Fitness', 'Study', 'Meditation', 'Diet', 'Productivity'],
                'values' => [1523, 892, 682, 456, 289]
            ]
        ];
        
        return $this->render('admin/statistics/user_stats.html.twig', $data);
    }



    #[Route('/admin/statistics/global', name: 'admin_global_stats')]
    public function globalStats(): Response
    {
        // Sample data for global statistics
        $data = [
            // Top 4 Cards
            'totalUsers' => 5247,
            'monthGrowth' => 18,
            'yearGrowth' => 145,
            'newUsersMonth' => 892,
            
            'totalCoaches' => 156,
            'averageRating' => 4.7,
            'totalSessions' => 3842,
            
            'totalRoutines' => 12847,
            'activeRoutines' => 8234,
            'completedRoutines' => 4613,
            'newRoutinesWeek' => 234,
            
            'activeSessions' => 47,
            'scheduledToday' => 89,
            'completedToday' => 42,
            
            // Content Statistics
            'contentStats' => [
                'posts' => 3456,
                'comments' => 8923,
                'chatrooms' => 234,
                'claims' => 156
            ],
            
            // Platform Growth (12 months)
            'growthData' => [
                'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'users' => [320, 412, 389, 456, 523, 598, 645, 712, 789, 856, 923, 892],
                'coaches' => [8, 12, 15, 18, 22, 25, 28, 32, 35, 38, 42, 45],
                'routines' => [450, 523, 612, 698, 756, 834, 912, 1023, 1145, 1234, 1312, 1289]
            ],
            
            // User Growth Forecast (AI Prediction)
            'forecastData' => [
                'labels' => ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
                'historical' => [789, 856, 923, 892, null, null, null],
                'predicted' => [null, null, null, 892, 945, 1012, 1089],
                'predictedGrowth' => 22,
                'confidence' => 87,
                'expectedUsers' => 6336,
                'currentMonthIndex' => 3
            ],
            
            // Churn Risk Prediction (AI)
            'churnData' => [
                'riskPercentage' => 28,
                'usersAtRisk' => 1469,
                'factors' => [
                    'lowEngagement' => 75,
                    'inactiveRoutines' => 45,
                    'noCoachInteraction' => 25
                ]
            ]
        ];
        
        return $this->render('admin/statistics/global_stats.html.twig', $data);
    }
}