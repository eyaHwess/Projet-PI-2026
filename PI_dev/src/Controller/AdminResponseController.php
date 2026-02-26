<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Response as ReclamationResponse;
use App\Form\ResponseType;
use App\Repository\ReclamationRepository;
use App\Enum\ReclamationStatusEnum;
use App\Enum\ReclamationTypeEnum;
use App\Service\ReclamationNotificationService;
use App\Service\AIResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/reclamation')]
class AdminResponseController extends AbstractController
{
   
    #[Route('/', name: 'admin_reclamation_list')]
    public function list(Request $request, ReclamationRepository $repository, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Get filter parameters
        $status = $request->query->get('status');
        $type = $request->query->get('type');
        $search = $request->query->get('search');

        // Build query criteria
        $criteria = [];
        if ($status) {
            $criteria['status'] = ReclamationStatusEnum::from($status);
        }
        if ($type) {
            $criteria['type'] = ReclamationTypeEnum::from($type);
        }

        // Get reclamations with filters
        $queryBuilder = $repository->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->orderBy('r.createdAt', 'DESC');

        if ($status) {
            $queryBuilder->andWhere('r.status = :status')
                        ->setParameter('status', ReclamationStatusEnum::from($status));
        }
        if ($type) {
            $queryBuilder->andWhere('r.type = :type')
                        ->setParameter('type', ReclamationTypeEnum::from($type));
        }
        if ($search) {
            $queryBuilder->andWhere('r.content LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
                        ->setParameter('search', '%' . $search . '%');
        }

        // Paginate the results
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10 // Items per page
        );

        return $this->render('admin_response/index.html.twig', [
            'reclamations' => $pagination,
            'currentFilters' => [
                'status' => $status,
                'type' => $type,
                'search' => $search
            ],
            'statusOptions' => ReclamationStatusEnum::cases(),
            'typeOptions' => ReclamationTypeEnum::cases()
        ]);
    }

    
    #[Route('/{id}/reply', name: 'admin_reclamation_reply')]
    public function reply(
        Request $request,
        Reclamation $reclamation,
        EntityManagerInterface $em,
        ReclamationNotificationService $notificationService,
        AIResponseService $aiService
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Generate AI-suggested response
        $aiSuggestion = $aiService->generateSuggestedResponse($reclamation);

        $response = new ReclamationResponse();
        $response->setReclamation($reclamation);
        // Pre-fill with AI suggestion
        $response->setContent($aiSuggestion);

        $form = $this->createForm(ResponseType::class, $response);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Change status to ANSWERED
            $reclamation->setStatus(ReclamationStatusEnum::ANSWERED);

            $em->persist($response);
            $em->flush();

            // Send notification to user
            try {
                $notificationService->notifyReclamationResponse($reclamation, $response);
            } catch (\Exception $e) {
                error_log('Failed to send response notification: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Réponse envoyée avec succès. L\'utilisateur a été notifié par email.');

            return $this->redirectToRoute('admin_reclamation_list');
        }

        return $this->render('admin_response/reply.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation,
            'aiSuggestion' => $aiSuggestion
        ]);
    }
}
