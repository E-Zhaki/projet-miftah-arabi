<?php

namespace App\Controller\Admin\Contact;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class ContactController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContactMessageRepository $contactMessageRepository,
    ) {
    }

    #[Route('/contact/list', name: 'app_admin_contact_index', methods: ['GET'])]
    public function index(): Response
    {
        $contacts = $this->contactMessageRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('pages/admin/contact/index.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    #[Route('/contact/{id<\d+>}/mark-as-processed', name: 'app_admin_contact_mark_as_processed', methods: ['POST'])]
    public function markAsProcessed(ContactMessage $contact, Request $request): Response
    {
        if ($this->isCsrfTokenValid("mark-contact-{$contact->getId()}", $request->request->get('csrf_token'))) {
            $contact->setIsProcessed(true);

            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le message a été marqué comme traité.');
        }

        return $this->redirectToRoute('app_admin_contact_index');
    }

    #[Route('/contact/{id<\d+>}/delete', name: 'app_admin_contact_delete', methods: ['POST'])]
    public function delete(ContactMessage $contact, Request $request): Response
    {
        if ($this->isCsrfTokenValid("delete-contact-{$contact->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($contact);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le message a été supprimé.');
        }

        return $this->redirectToRoute('app_admin_contact_index');
    }
}
