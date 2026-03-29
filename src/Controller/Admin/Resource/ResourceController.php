<?php

namespace App\Controller\Admin\Resource;

use App\Entity\Resource;
use App\Form\Admin\ResourceFormType;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class ResourceController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ResourceRepository $resourceRepository,
    ) {
    }

    #[Route('/resource/list', name: 'app_admin_resource_index', methods: ['GET'])]
    public function index(): Response
    {
        $resources = $this->resourceRepository->findAll();

        return $this->render('pages/admin/resource/index.html.twig', [
            'resources' => $resources,
        ]);
    }

    #[Route('/resource/create', name: 'app_admin_resource_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $resource = new Resource();

        $form = $this->createForm(ResourceFormType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resource->setCreatedAt(new \DateTimeImmutable());
            $resource->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $this->addFlash('success', 'La ressource a été ajoutée avec succès.');

            return $this->redirectToRoute('app_admin_resource_index');
        }

        return $this->render('pages/admin/resource/create.html.twig', [
            'resourceForm' => $form->createView(),
        ]);
    }

    #[Route('/resource/{id<\d+>}/edit', name: 'app_admin_resource_edit', methods: ['GET', 'POST'])]
    public function edit(Resource $resource, Request $request): Response
    {
        $form = $this->createForm(ResourceFormType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resource->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $this->addFlash('success', 'La ressource a été modifiée avec succès.');

            return $this->redirectToRoute('app_admin_resource_index');
        }

        return $this->render('pages/admin/resource/edit.html.twig', [
            'resourceForm' => $form->createView(),
            'resource' => $resource,
        ]);
    }

    #[Route('/resource/{id<\d+>}/delete', name: 'app_admin_resource_delete', methods: ['POST'])]
    public function delete(Resource $resource, Request $request): Response
    {
        if ($this->isCsrfTokenValid("delete-resource-{$resource->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($resource);
            $this->entityManager->flush();

            $this->addFlash('success', 'La ressource a été supprimée.');
        }

        return $this->redirectToRoute('app_admin_resource_index');
    }
}
