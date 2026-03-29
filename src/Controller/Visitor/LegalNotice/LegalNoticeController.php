<?php

namespace App\Controller\Visitor\LegalNotice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalNoticeController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_visitor_legal_notice', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/visitor/legal_notice/index.html.twig');
    }
}