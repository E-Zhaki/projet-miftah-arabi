<?php

namespace App\Controller\Admin\Lesson;

use App\Entity\Lesson;
use App\Form\Admin\LessonFormType;
use App\Repository\CategoryRepository;
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
    ) {
    }

    #[Route('/lesson/list', name: 'app_admin_lesson_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/lesson/index.html.twig');
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
}
