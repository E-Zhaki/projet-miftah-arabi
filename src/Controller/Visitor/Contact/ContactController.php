<?php

namespace App\Controller\Visitor\Contact;

use App\Entity\ContactMessage;
use App\Form\ContactMessageFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_visitor_contact_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contactMessage = new ContactMessage();

        $form = $this->createForm(ContactMessageFormType::class, $contactMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactMessage->setCreatedAt(new \DateTimeImmutable());
            $contactMessage->setIsProcessed(false);

            $entityManager->persist($contactMessage);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('app_visitor_contact_index');
        }

        return $this->render('pages/visitor/contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}
