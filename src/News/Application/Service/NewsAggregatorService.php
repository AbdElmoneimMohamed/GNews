<?php

declare(strict_types=1);

namespace App\News\Application\Service;

use App\News\Domain\Entity\NewsArticle;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use App\News\Infrastructure\ExternalApi\GNews\GNewsApiServiceInterface;
use Psr\Log\LoggerInterface;

class NewsAggregatorService
{
    public function __construct(
        private readonly GNewsApiServiceInterface $gNewsApiService,
        private readonly NewsArticleRepositoryInterface $repository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array{saved: int, updated: int, skipped: int, errors: int}
     */
    public function ingestArticles(array $parameters): array
    {
        $stats = [
            'saved' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        try {
            $articles = $this->gNewsApiService->fetchArticles($parameters);

            foreach ($articles as $article) {
                try {
                    $existing = $this->repository->findByExternalId($article->getExternalId());

                    if ($existing !== null) {
                        if ($existing->hasContentChanged($article->getContent(), $article->getPublishedAt())) {
                            $this->updateExistingArticle($existing, $article);
                            $this->repository->save($existing);
                            ++$stats['updated'];

                            $this->logger->info('Article updated', [
                                'article_id' => $article->getExternalId()->toString(),
                            ]);
                        } else {
                            ++$stats['skipped'];
                        }

                        continue;
                    }

                    $this->repository->save($article);
                    ++$stats['saved'];
                } catch (\Exception $e) {
                    ++$stats['errors'];
                    $this->logger->error('Failed to save article', [
                        'error' => $e->getMessage(),
                        'article_id' => $article->getExternalId()->toString(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to ingest articles', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $stats;
    }

    /**
     * Get articles with filters and pagination.
     *
     * @param array<string, mixed> $filters
     *
     * @return array{articles: array<NewsArticle>, total: int}
     */
    public function getArticles(array $filters, int $page = 1, int $limit = 10): array
    {
        $articles = $this->repository->findWithFilters($filters, $page, $limit);
        $total = $this->repository->countWithFilters($filters);

        return [
            'articles' => $articles,
            'total' => $total,
        ];
    }

    private function updateExistingArticle(NewsArticle $existing, NewsArticle $new): void
    {
        $existing->updateContent($new->getContent());
        $existing->updateSource($new->getSource());
        $existing->updateUrl($new->getUrl());
        $existing->updateImageUrl($new->getImageUrl());
        $existing->updatePublishedAt($new->getPublishedAt());
    }
}
