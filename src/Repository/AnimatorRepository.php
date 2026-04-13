<?php

namespace App\Repository;

use App\Entity\Animator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Animator>
 */
class AnimatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animator::class);
    }

    public function countForAdminList(?string $search, ?string $active): int
    {
        $qb = $this->createQueryBuilder('a')->select('COUNT(a.id)');
        $this->applyAdminListFilters($qb, $search, $active);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return list<Animator>
     */
    public function findPageForAdminList(int $offset, int $limit, ?string $search, ?string $active): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $this->applyAdminListFilters($qb, $search, $active);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return list<Animator>
     */
    public function findAllForAdminExport(?string $search, ?string $active): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC');
        $this->applyAdminListFilters($qb, $search, $active);

        return $qb->getQuery()->getResult();
    }

    private function applyAdminListFilters(QueryBuilder $qb, ?string $search, ?string $active): void
    {
        if ($search !== null && $search !== '') {
            $term = '%' . mb_strtolower($search, 'UTF-8') . '%';
            $qb->andWhere($qb->expr()->orX(
                'LOWER(COALESCE(a.name, \'\')) LIKE :animSearch',
                'LOWER(COALESCE(a.title, \'\')) LIKE :animSearch',
                'LOWER(COALESCE(a.description, \'\')) LIKE :animSearch',
                'LOWER(COALESCE(a.category, \'\')) LIKE :animSearch',
            ))->setParameter('animSearch', $term);
        }

        if ($active === '1') {
            $qb->andWhere('a.isActive = :animAct')->setParameter('animAct', true);
        } elseif ($active === '0') {
            $qb->andWhere('a.isActive = :animAct')->setParameter('animAct', false);
        }
    }
}
