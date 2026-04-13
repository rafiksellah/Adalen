<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function countForAdminList(?string $search, ?string $active): int
    {
        $qb = $this->createQueryBuilder('act')->select('COUNT(act.id)');
        $this->applyAdminListFilters($qb, $search, $active);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return list<Activity>
     */
    public function findPageForAdminList(int $offset, int $limit, ?string $search, ?string $active): array
    {
        $qb = $this->createQueryBuilder('act')
            ->orderBy('act.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $this->applyAdminListFilters($qb, $search, $active);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return list<Activity>
     */
    public function findAllForAdminExport(?string $search, ?string $active): array
    {
        $qb = $this->createQueryBuilder('act')
            ->orderBy('act.createdAt', 'DESC');
        $this->applyAdminListFilters($qb, $search, $active);

        return $qb->getQuery()->getResult();
    }

    private function applyAdminListFilters(QueryBuilder $qb, ?string $search, ?string $active): void
    {
        if ($search !== null && $search !== '') {
            $term = '%' . mb_strtolower($search, 'UTF-8') . '%';
            $qb->andWhere($qb->expr()->orX(
                'LOWER(COALESCE(act.name, \'\')) LIKE :actSearch',
                'LOWER(COALESCE(act.description, \'\')) LIKE :actSearch',
                'LOWER(COALESCE(act.icon, \'\')) LIKE :actSearch',
                'LOWER(COALESCE(act.ageRange, \'\')) LIKE :actSearch',
                'LOWER(COALESCE(act.duration, \'\')) LIKE :actSearch',
            ))->setParameter('actSearch', $term);
        }

        if ($active === '1') {
            $qb->andWhere('act.isActive = :actActive')->setParameter('actActive', true);
        } elseif ($active === '0') {
            $qb->andWhere('act.isActive = :actActive')->setParameter('actActive', false);
        }
    }
}
