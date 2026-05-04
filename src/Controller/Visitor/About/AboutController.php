<?php

namespace App\Controller\Visitor\About;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route('/a-propos', name: 'app_visitor_about', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/visitor/about/index.html.twig');
    }
}
