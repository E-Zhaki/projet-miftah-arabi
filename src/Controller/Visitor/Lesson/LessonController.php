<?php

namespace App\Controller\Visitor\Lesson;

use App\Entity\Category;
use App\Entity\Tag;
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

    #[Route('/lecon', name: 'app_visitor_lesson_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $levels = [
            'Débutant' => 'debutant',
            'Intermédiaire' => 'intermediaire',
            'Avancé' => 'avance',
        ];

        $query = $this->lessonRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC']
        );

        $lessons = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/visitor/lesson/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'levels' => $levels,
            'lessons' => $lessons,
        ]);
    }

    #[Route('/lecon/{id<\d+>}/{slug}', name: 'app_visitor_lesson_show', methods: ['GET'])]
    public function show(int $id, string $slug): Response
    {
        $lesson = $this->lessonRepository->findOneBy([
            'id' => $id,
            'slug' => $slug,
            'isPublished' => true,
        ]);

        if (!$lesson) {
            throw $this->createNotFoundException('Leçon introuvable.');
        }

        return $this->render('pages/visitor/lesson/show.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    #[Route('/lecon/lecons-filtre-par-categorie/{id<\d+>}/{slug}', name: 'app_visitor_lesson_filter_by_category', methods: ['GET'])]
    public function filterLessonsByCategory(Category $category, Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $levels = [
            'Débutant' => 'debutant',
            'Intermédiaire' => 'intermediaire',
            'Avancé' => 'avance',
        ];

        $query = $this->lessonRepository->findBy(
            ['category' => $category, 'isPublished' => true],
            ['publishedAt' => 'DESC']
        );

        $lessons = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/visitor/lesson/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'levels' => $levels,
            'lessons' => $lessons,
        ]);
    }

    #[Route('/lecon/lecons-filtre-par-tag/{id<\d+>}/{slug}', name: 'app_visitor_lesson_filter_by_tag', methods: ['GET'])]
    public function filterLessonsByTag(Tag $tag, Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $levels = [
            'Débutant' => 'debutant',
            'Intermédiaire' => 'intermediaire',
            'Avancé' => 'avance',
        ];

        $query = $this->lessonRepository->filterLessonsByTag($tag->getId());

        $lessons = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/visitor/lesson/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'levels' => $levels,
            'lessons' => $lessons,
        ]);
    }

    #[Route('/lecon/lecons-filtre-par-niveau/{level}', name: 'app_visitor_lesson_filter_by_level', methods: ['GET'])]
    public function filterLessonsByLevel(string $level, Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $levels = [
            'Débutant' => 'debutant',
            'Intermédiaire' => 'intermediaire',
            'Avancé' => 'avance',
        ];

        $allowedLevels = array_values($levels);

        if (!in_array($level, $allowedLevels, true)) {
            throw $this->createNotFoundException('Niveau introuvable.');
        }

        $query = $this->lessonRepository->findBy(
            ['level' => $level, 'isPublished' => true],
            ['publishedAt' => 'DESC']
        );

        $lessons = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/visitor/lesson/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'levels' => $levels,
            'lessons' => $lessons,
        ]);
    }
}