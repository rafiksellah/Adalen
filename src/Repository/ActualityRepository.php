<?php

namespace App\Repository;

use App\Entity\Actuality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Actuality>
 */
class ActualityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actuality::class);
    }

    public function save(Actuality $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Actuality $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupère les actualités publiées, triées par date de création décroissante
     */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countForAdminList(?string $search, ?string $published): int
    {
        $qb = $this->createQueryBuilder('a')->select('COUNT(a.id)');
        $this->applyAdminListFilters($qb, $search, $published);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return list<Actuality>
     */
    public function findPageForAdminList(int $offset, int $limit, ?string $search, ?string $published): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $this->applyAdminListFilters($qb, $search, $published);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return list<Actuality>
     */
    public function findAllForAdminExport(?string $search, ?string $published): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC');
        $this->applyAdminListFilters($qb, $search, $published);

        return $qb->getQuery()->getResult();
    }

    private function applyAdminListFilters(QueryBuilder $qb, ?string $search, ?string $published): void
    {
        if ($search !== null && $search !== '') {
            $term = '%' . mb_strtolower($search, 'UTF-8') . '%';
            $qb->andWhere($qb->expr()->orX(
                'LOWER(COALESCE(a.titleFr, \'\')) LIKE :actSearch',
                'LOWER(COALESCE(a.titleEn, \'\')) LIKE :actSearch',
                'LOWER(COALESCE(a.titleAr, \'\')) LIKE :actSearch',
                'LOWER(COALESCE(a.descriptionFr, \'\')) LIKE :actSearch',
                'LOWER(COALESCE(a.descriptionEn, \'\')) LIKE :actSearch',
                'LOWER(COALESCE(a.descriptionAr, \'\')) LIKE :actSearch',
            ))->setParameter('actSearch', $term);
        }

        if ($published === '1') {
            $qb->andWhere('a.isPublished = :actPub')->setParameter('actPub', true);
        } elseif ($published === '0') {
            $qb->andWhere('a.isPublished = :actPub')->setParameter('actPub', false);
        }
    }
}
