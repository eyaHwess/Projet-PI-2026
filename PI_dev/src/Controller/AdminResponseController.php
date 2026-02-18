<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Response as ReclamationResponse;
use App\Form\ResponseType;
use App\Repository\ReclamationRepository;
use App\Enum\ReclamationStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/reclamation')]
class AdminResponseController extends AbstractController
{
   
    #[Route('/', name: 'admin_reclamation_list')]
    public function list(ReclamationRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin_response/index.html.twig', [
            'reclamations' => $repository->findBy([], ['createdAt' => 'DESC'])
        ]);
    }

    
    #[Route('/{id}/reply', name: 'admin_reclamation_reply')]
    public function reply(
        Request $request,
        Reclamation $reclamation,
        EntityManagerInterface $em
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $response = new ReclamationResponse();
        $response->setReclamation($reclamation);

        $form = $this->createForm(ResponseType::class, $response);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Change status to ANSWERED
            $reclamation->setStatus(ReclamationStatusEnum::ANSWERED);

            $em->persist($response);
            $em->flush();

            $this->addFlash('success', 'Response sent successfully.');

            return $this->redirectToRoute('admin_reclamation_list');
        }

        return $this->render('admin_response/reply.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation
        ]);
    }
}
