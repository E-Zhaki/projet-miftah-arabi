<?php

namespace App\Repository;

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

    /**
     * Cette méthode filtre les Lessons en fonction du tag précisé.
     *
     * @return array<int, Lesson>
     */
    public function filterLessonsByTag(int $tag_id): array
    {
        return $this->createQueryBuilder('p')
                    ->join('p.tags', 't')
                    ->select('p')
                    ->where('t.id = :id')
                    ->andWhere('p.isPublished = :val')
                    ->setParameter('id', $tag_id)
                    ->setParameter('val', true)
                    ->orderBy('p.publishedAt', 'DESC')
                    ->getQuery()
                    ->getResult();
    }

    //    /**
    //     * @return Lesson[] Returns an array of Lesson objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Lesson
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
