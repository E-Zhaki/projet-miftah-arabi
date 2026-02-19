<?php

namespace App\Controller\Admin\Lesson;

use App\Entity\Lesson;
use App\Form\Admin\LessonFormType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class LessonController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
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
            dd('yes');
        }

        return $this->render('pages/admin/lesson/create.html.twig', [
            'lessonForm' => $form->createView(),
        ]);
    }
}
