<?php

namespace App\Controller\User\FavoriteList;

use App\Entity\FavoriteList;
use App\Entity\Lesson;
use App\Repository\FavoriteListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class FavoriteListController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FavoriteListRepository $favoriteListRepository,
    ) {
    }

    #[Route('/favoris', name: 'app_user_favorite_list_index', methods: ['GET'])]
    public function index(): Response
    {
        $favoriteLists = $this->favoriteListRepository->findBy([
            'user' => $this->getUser(),
        ]);

        return $this->render('pages/user/favorite_list/index.html.twig', [
            'favoriteLists' => $favoriteLists,
        ]);
    }

    #[Route('/favoris/{id<\d+>}/add', name: 'app_user_favorite_list_add', methods: ['POST'])]
    public function add(Lesson $lesson, Request $request): Response
    {
        if ($this->isCsrfTokenValid("add-favorite-{$lesson->getId()}", $request->request->get('csrf_token'))) {
            $existing = $this->favoriteListRepository->findOneBy([
                'user' => $this->getUser(),
                'lesson' => $lesson,
            ]);

            if (!$existing) {
                $favorite = new FavoriteList();
                $favorite->setUser($this->getUser());
                $favorite->setLesson($lesson);
                $favorite->setCreatedAt(new \DateTimeImmutable());

                $this->entityManager->persist($favorite);
                $this->entityManager->flush();

                $this->addFlash('success', 'La leçon a été ajoutée à vos favoris.');
            } else {
                $this->addFlash('info', 'Cette leçon est déjà dans vos favoris.');
            }
        }

        return $this->redirectToRoute('app_visitor_lesson_show', [
            'id' => $lesson->getId(),
            'slug' => $lesson->getSlug(),
        ]);
    }

    #[Route('/favoris/{id<\d+>}/remove', name: 'app_user_favorite_list_remove', methods: ['POST'])]
    public function remove(FavoriteList $favoriteList, Request $request): Response
    {
        if ($this->isCsrfTokenValid("remove-favorite-{$favoriteList->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($favoriteList);
            $this->entityManager->flush();

            $this->addFlash('success', 'La leçon a été retirée de vos favoris.');
        }

        return $this->redirectToRoute('app_user_favorite_list_index');
    }
}