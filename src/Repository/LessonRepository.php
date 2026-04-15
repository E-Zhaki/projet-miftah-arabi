<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    public function countByCategory(Category $category): int
    {
        return (int) $this->createQueryBuilder('lesson')
            ->select('COUNT(lesson.id)')
            ->where('lesson.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Cette méthode filtre les lessons en fonction du tag précisé.
     *
     * @return array<int, Lesson>
     */
    public function filterLessonsByTag(int $tagId): array
    {
        return $this->createQueryBuilder('lesson')
            ->join('lesson.tags', 'tag')
            ->select('lesson')
            ->where('tag.id = :id')
            ->andWhere('lesson.isPublished = :isPublished')
            ->setParameter('id', $tagId)
            ->setParameter('isPublished', true)
            ->orderBy('lesson.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}