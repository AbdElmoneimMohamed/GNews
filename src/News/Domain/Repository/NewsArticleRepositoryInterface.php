<?php

declare(strict_types=1);

namespace App\News\Domain\Repository;

use App\News\Domain\Entity\NewsArticle;
use App\News\Domain\ValueObject\ExternalId;

interface NewsArticleRepositoryInterface
{
    public function save(NewsArticle $article): void;

    public function findById(int $id): ?NewsArticle;

    public function findByExternalId(ExternalId $externalId): ?NewsArticle;

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<NewsArticle>
     */
    public function findWithFilters(array $filters, int $page, int $limit): array;

    /**
     * @param array<string, mixed> $filters
     */
    public function countWithFilters(array $filters): int;
}
