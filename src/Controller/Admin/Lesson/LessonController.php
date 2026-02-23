<?php

namespace App\Controller\Admin\Lesson;

use App\Entity\Lesson;
use App\Entity\User;
use App\Form\Admin\LessonFormType;
use App\Repository\CategoryRepository;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class LessonController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LessonRepository $lessonRepository,
    ) {
    }

    #[Route('/lesson/list', name: 'app_admin_lesson_index', methods: ['GET'])]
    public function index(): Response
    {
        $lessons = $this->lessonRepository->findAll();

        return $this->render('pages/admin/lesson/index.html.twig', [
            'lessons' => $lessons,
        ]);
    }

    #[Route('/lesson/create', name: 'app_admin_lesson_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        if (0 == $this->categoryRepository->count()) {
            $this->addFlash('warning', 'Pour rédiger des leçons, vous devez avoir une catégorie.');

            return $this->redirectToRoute('app_admin_category_index');
        }

        $lesson = new Lesson();

        $form = $this->createForm(LessonFormType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var User
             */
            $admin = $this->getUser();

            $lesson->setUser($admin);
            $lesson->setCreatedAt(new \DateTimeImmutable());
            $lesson->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $this->addFlash('success', 'La leçon a été ajoutée avec succés.');

            return $this->redirectToRoute('app_admin_lesson_index');
        }

        return $this->render('pages/admin/lesson/create.html.twig', [
            'lessonForm' => $form->createView(),
        ]);
    }

    #[Route('/lesson/{id<\d+>}/show', name: 'app_admin_lesson_show', methods: ['GET'])]
    public function show(Lesson $lesson): Response
    {
        return $this->render('pages/admin/lesson/show.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    #[Route('/lesson/{id<\d+>}/edit', name: 'app_admin_lesson_edit', methods: ['GET', 'POST'])]
    public function edit(Lesson $lesson, Request $request): Response
    {
        $form = $this->createForm(LessonFormType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var User
             */
            $admin = $this->getUser();

            $lesson->setUser($admin);
            $lesson->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($lesson);
            $this->entityManager->flush();

            $this->addFlash('success', 'La leçon a été modifiée avec succés.');

            return $this->redirectToRoute('app_admin_lesson_index');
        }

        return $this->render('pages/admin/lesson/edit.html.twig', [
            'lessonForm' => $form->createView(),
            'lesson' => $lesson,
        ]);
    }

    #[Route('/lesson/{id<\d+>}/delete', name: 'app_admin_lesson_delete', methods: ['POST'])]
    public function delete(Lesson $lesson, Request $request): Response
    {
        if ($this->isCsrfTokenValid("delete-lesson-{$lesson->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($lesson);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'article a été supprimé');
        }

        return $this->redirectToRoute('app_admin_lesson_index');
    }

    #[Route('/lesson/{id<\d+>}/publish', name: 'app_admin_lesson_publish', methods: ['POST'])]
    public function publish(Lesson $lesson, Request $request): Response
    {
        if (!$this->isCsrfTokenValid("publish-lesson-{$lesson->getId()}", $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_admin_lesson_index');
        }

        // Si l'article est non publié
        if (!$lesson->isPublished()) {
            // Publions-le
            $lesson->setIsPublished(true);

            // Mettons à jour sa date de publication
            $lesson->setPublishedAt(new \DateTimeImmutable());

            // Générons le message flash correspondant
            $this->addFlash('success', "L'article a été publié.");
        } else {
            // Dans le cas contraire,

            // Retirons l'article de la liste des publications
            $lesson->setIsPublished(false);

            // Mettons à jour sa date de publication
            $lesson->setPublishedAt(null);

            // Générons le message flash correspondant
            $this->addFlash('success', "L'article a été retiré de la liste des publications.");
        }

        // Demandons au manager des entités de sauvegarder les modifications apportées en base de données
        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        // Rediriger l'administrateur vers la route menant à la page de listant les articles
        // Puis, arrêtons l'exécution du script.
        return $this->redirectToRoute('app_admin_lesson_index');
    }
}
