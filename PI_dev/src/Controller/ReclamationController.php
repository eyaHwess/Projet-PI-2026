<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Response as ReclamationResponse;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use App\Enum\ReclamationStatusEnum;
use App\Service\ReclamationNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    // 🔹 USER - List his reclamations
    #[Route('/', name: 'reclamation_index')]
    public function index(Request $request, ReclamationRepository $repository, PaginatorInterface $paginator): Response
    {
        if (
    !$this->isGranted('ROLE_USER') &&
    !$this->isGranted('ROLE_ADMIN')
) {
    throw $this->createAccessDeniedException();
}

        $queryBuilder = $repository->createQueryBuilder('r')
            ->where('r.user = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('r.createdAt', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5 // Items per page for user view
        );

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $pagination
        ]);
    }

    
    #[Route('/new', name: 'reclamation_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ReclamationNotificationService $notificationService
    ): Response {

        if (
    !$this->isGranted('ROLE_USER') &&
    !$this->isGranted('ROLE_ADMIN')
) {
    throw $this->createAccessDeniedException();
}

        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle photo upload
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Simple filename sanitization without intl extension
                $safeFilename = preg_replace('/[^A-Za-z0-9\-_]/', '', $originalFilename);
                if (empty($safeFilename)) {
                    $safeFilename = 'reclamation';
                }
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/reclamations';
                    if (!is_dir($uploadsDirectory)) {
                        mkdir($uploadsDirectory, 0755, true);
                    }
                    $photoFile->move($uploadsDirectory, $newFilename);
                    $reclamation->setPhotoPath('uploads/reclamations/'.$newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo.');
                }
            }

            // Link to logged user
            $reclamation->setUser($this->getUser());
            $reclamation->setStatus(ReclamationStatusEnum::PENDING);

            $em->persist($reclamation);

            $autoResponse = new ReclamationResponse();
            $autoResponse->setContent(
                "Votre réclamation a été reçue et est en cours d'examen. Notre équipe vous répondra dans les plus brefs délais."
            );
            $autoResponse->setReclamation($reclamation);

            $em->persist($autoResponse);
            $em->flush();

            // Send notifications
            try {
                $notificationService->notifyNewReclamation($reclamation);
            } catch (\Exception $e) {
                // Log error but don't fail the request
                error_log('Failed to send notifications: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Réclamation soumise avec succès.');

            return $this->redirectToRoute('reclamation_index');
        }

        return $this->render('reclamation/reclamation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    
    #[Route('/show/{id}', name: 'reclamation_show')]
    public function show(Reclamation $reclamation): Response
{
    if (
        !$this->isGranted('ROLE_USER') &&
        !$this->isGranted('ROLE_ADMIN')
    ) {
        throw $this->createAccessDeniedException();
    }

    // If normal user, restrict to his own reclamations
    if (
        $this->isGranted('ROLE_USER') &&
        !$this->isGranted('ROLE_ADMIN') &&
        $reclamation->getUser() !== $this->getUser()
    ) {
        throw $this->createAccessDeniedException();
    }

    return $this->render('reclamation/show.html.twig', [
        'reclamation' => $reclamation
    ]);
}

}
