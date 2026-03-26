<?php

namespace App\Controller\Admin\Home;

use App\Repository\CategoryRepository;
use App\Repository\ContactMessageRepository;
use App\Repository\LessonRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class HomeController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ContactMessageRepository $contactMessageRepository,
        private readonly LessonRepository $lessonRepository,
        private readonly TagRepository $tagRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/home', name: 'app_admin_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/home/index.html.twig', [
            'categories_counted' => $this->categoryRepository->count(),
            'lessons_counted' => $this->lessonRepository->count(),
            'tags_counted' => $this->tagRepository->count(),
            'users_counted' => $this->userRepository->count(),
            'contacts_counted' => $this->contactMessageRepository->count(),
        ]);
    }
}