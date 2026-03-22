<?php

namespace App\Controller\Visitor\Lesson;

use App\Repository\CategoryRepository;
use App\Repository\LessonRepository;
use App\Repository\TagRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LessonController extends AbstractController
{
    public function __construct(
        private readonly LessonRepository $lessonRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository $tagRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[Route('/leçons', name: 'app_visitor_lesson_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $query = $this->lessonRepository->findBy(['isPublished' => true]);

        $lessons = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        return $this->render('pages/visitor/lesson/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'lessons' => $lessons,
        ]);
    }
}
