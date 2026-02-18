<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Response as ReclamationResponse;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use App\Enum\ReclamationStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    // 🔹 USER - List his reclamations
    #[Route('/', name: 'reclamation_index')]
    public function index(ReclamationRepository $repository): Response
    {
        if (
    !$this->isGranted('ROLE_USER') &&
    !$this->isGranted('ROLE_ADMIN')
) {
    throw $this->createAccessDeniedException();
}


        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $repository->findBy(
                ['user' => $this->getUser()],
                ['createdAt' => 'DESC']
            )
        ]);
    }

    
    #[Route('/new', name: 'reclamation_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em
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

            // Link to logged user
    
            $reclamation->setUser($this->getUser());
            $reclamation->setStatus(ReclamationStatusEnum::PENDING);

            $em->persist($reclamation);

            
            $autoResponse = new ReclamationResponse();
            $autoResponse->setContent(
                "Your reclamation has been received and is under consideration."
            );
            $autoResponse->setReclamation($reclamation);

            $em->persist($autoResponse);
            $em->flush();

            $this->addFlash('success', 'Reclamation submitted successfully.');

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
