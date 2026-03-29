<?php

namespace App\Controller\Visitor\SiteMap;

use App\Repository\LessonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SiteMapController extends AbstractController
{
    public function __construct(
        private readonly LessonRepository $lessonRepository,
    ) {
    }

    #[Route('/sitemap.xml', name: 'app_visitor_sitemap_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $hostName = $request->getSchemeAndHttpHost();

        $urls = [];
        $urls[] = [
            'loc' => $this->generateUrl('app_visitor_welcome'),
        ];

        $lessons = $this->lessonRepository->findBy(['isPublished' => true], ['publishedAt' => 'DESC']);

        foreach ($lessons as $lesson) {
            $urls[] = [
                'loc' => $this->generateUrl('app_visitor_lesson_show', ['id' => $lesson->getId(), 'slug' => $lesson->getSlug()]),
                'lastmod' => $lesson->getUpdatedAt()->format('Y-m-d'),
                'priority' => 0.9,
                'changefreq' => 'weekly',
            ];
        }

        $response = $this->render('pages/visitor/site_map/index.html.twig', [
            'host_name' => $hostName,
            'urls' => $urls,
        ]);

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
