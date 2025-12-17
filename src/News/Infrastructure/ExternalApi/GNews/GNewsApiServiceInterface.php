<?php

declare(strict_types=1);

namespace App\News\Infrastructure\ExternalApi\GNews;

use App\News\Domain\Entity\NewsArticle;

interface GNewsApiServiceInterface
{
    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<NewsArticle>
     */
    public function fetchArticles(array $parameters): array;
}
