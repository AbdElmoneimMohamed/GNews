<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Persistence\Doctrine\Repository;

use App\News\Domain\Entity\NewsArticle;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use App\News\Domain\ValueObject\ExternalId;
use App\News\Infrastructure\Persistence\Doctrine\Entity\NewsArticleEntity;
use App\News\Infrastructure\Persistence\Doctrine\Mapper\NewsArticleMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NewsArticleEntity>
 */
final class NewsArticleRepository extends ServiceEntityRepository implements NewsArticleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsArticleEntity::class);
    }

    public function save(NewsArticle $article): void
    {
        $externalId = $article->getExternalId()->toString();
        $entity = $this->findOneBy([
            'externalId' => $externalId,
        ]);

        if ($entity === null) {
            $entity = NewsArticleMapper::mapArticalModelToArticalEntity($article);
        } else {
            NewsArticleMapper::syncEntityFromDomainModel($entity, $article);
        }

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id): ?NewsArticle
    {
        $entity = $this->find($id);

        return $entity !== null ? NewsArticleMapper::mapArticalEntityToArticalModel($entity) : null;
    }

    public function findByExternalId(ExternalId $externalId): ?NewsArticle
    {
        $entity = $this->findOneBy([
            'externalId' => $externalId->toString(),
        ]);

        return $entity !== null ? NewsArticleMapper::mapArticalEntityToArticalModel($entity) : null;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<NewsArticle>
     */
    public function findWithFilters(array $filters, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('n');

        $this->applyFilters($qb, $filters);

        $qb->orderBy('n.publishedAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;

        $entities = $qb->getQuery()->getResult();

        return array_map(
            static fn (NewsArticleEntity $entity): NewsArticle => NewsArticleMapper::mapArticalEntityToArticalModel($entity),
            $entities,
        );
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function countWithFilters(array $filters): int
    {
        $qb = $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
        ;

        $this->applyFilters($qb, $filters);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyFilters(\Doctrine\ORM\QueryBuilder $qb, array $filters): void
    {
        if (isset($filters['keyword'])) {
            $qb->andWhere('n.title LIKE :keyword OR n.description LIKE :keyword OR n.content LIKE :keyword')
                ->setParameter('keyword', '%' . $filters['keyword'] . '%')
            ;
        }

        if (isset($filters['language'])) {
            $qb->andWhere('n.language = :language')
                ->setParameter('language', $filters['language'])
            ;
        }

        if (isset($filters['source'])) {
            $qb->andWhere('n.sourceName = :source')
                ->setParameter('source', $filters['source'])
            ;
        }

        if (isset($filters['from'])) {
            $qb->andWhere('n.publishedAt >= :from')
                ->setParameter('from', new \DateTimeImmutable($filters['from']))
            ;
        }

        if (isset($filters['to'])) {
            $qb->andWhere('n.publishedAt <= :to')
                ->setParameter('to', new \DateTimeImmutable($filters['to']))
            ;
        }
    }
}
