<?php

declare(strict_types=1);

namespace App\News\Infrastructure\ExternalApi\GNews;

use App\News\Domain\Entity\NewsArticle;
use App\News\Domain\ValueObject\ArticleContent;
use App\News\Domain\ValueObject\ExternalId;
use App\News\Domain\ValueObject\Language;
use App\News\Domain\ValueObject\Source;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GNewsApiService implements GNewsApiServiceInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<NewsArticle>
     */
    public function fetchArticles(array $parameters): array
    {
        $endpoint = $this->baseUrl . '/search';
        $queryParams = $this->buildQueryParameters($parameters);

        try {
            $response = $this->httpClient->request('GET', $endpoint, [
                'query' => $queryParams,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new \RuntimeException(\sprintf('GNews API returned status code: %d', $statusCode));
            }

            $data = $response->toArray();

            return $this->parseArticles($data['articles'] ?? []);
        } catch (\Exception $e) {
            $this->logger->error('GNews API request failed', [
                'error' => $e->getMessage(),
                'parameters' => $parameters,
            ]);

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    private function buildQueryParameters(array $parameters): array
    {
        $query = [
            'apikey' => $this->apiKey,
            'max' => $parameters['max'] ?? 10,
        ];

        if (isset($parameters['q'])) {
            $query['q'] = $parameters['q'];
        }

        if (isset($parameters['lang'])) {
            $query['lang'] = $parameters['lang'];
        }

        if (isset($parameters['country'])) {
            $query['country'] = $parameters['country'];
        }

        if (isset($parameters['from'])) {
            $query['from'] = $parameters['from'];
        }

        if (isset($parameters['to'])) {
            $query['to'] = $parameters['to'];
        }

        return $query;
    }

    /**
     * @param array<array<string, mixed>> $articlesData
     *
     * @return array<NewsArticle>
     */
    private function parseArticles(array $articlesData): array
    {
        $articles = [];

        foreach ($articlesData as $articleData) {
            try {
                $articles[] = $this->createArticle($articleData);
            } catch (\Exception $e) {
                $this->logger->warning('Failed to parse article', [
                    'error' => $e->getMessage(),
                    'article' => $articleData,
                ]);
            }
        }

        return $articles;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createArticle(array $data): NewsArticle
    {
        if (! isset($data['url'])) {
            throw new \InvalidArgumentException('Missing URL');
        }

        return NewsArticle::create(
            externalId: ExternalId::fromString($data['url']),
            content: ArticleContent::create(
                title: $data['title'] ?? '',
                description: $data['description'] ?? '',
                content: $data['content'] ?? '',
            ),
            source: Source::fromName($data['source']['name'] ?? 'Unknown'),
            url: $data['url'],
            imageUrl: $data['image'] ?? null,
            publishedAt: new \DateTimeImmutable($data['publishedAt'] ?? 'now'),
            language: Language::fromCode($data['language'] ?? 'en'),
        );
    }
}
