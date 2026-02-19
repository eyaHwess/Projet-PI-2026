<?php

namespace App\Controller;

use App\Entity\Chatroom;
use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatroomController extends AbstractController
{
    #[Route('/chatroom/{id}', name: 'chatroom_show')]
    public function show(
        Chatroom $chatroom,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        // Temporairement sans authentification pour tester le design
        $user = $this->getUser();
        $goal = $chatroom->getGoal();

        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($user) {
                $message->setAuthor($user);
                $message->setChatroom($chatroom);
                $message->setCreatedAt(new \DateTime());

                $em->persist($message);
                $em->flush();
            }

            return $this->redirectToRoute('chatroom_show', [
                'id' => $chatroom->getId()
            ]);
        }

        return $this->render('chatroom/chatroom.html.twig', [
            'chatroom' => $chatroom,
            'goal' => $goal,
            'form' => $form->createView(),
        ]);
    }
}
