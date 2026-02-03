<?php

namespace App\Controller\Visitor\Lesson;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LessonController extends AbstractController
{
    #[Route('/leçons', name: 'app_visitor_lesson_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/visitor/lesson/index.html.twig');
    }
}
