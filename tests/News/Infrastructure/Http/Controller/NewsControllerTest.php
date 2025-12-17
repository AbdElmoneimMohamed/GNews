<?php

declare(strict_types=1);

namespace App\Tests\News\Infrastructure\Http\Controller;

use App\News\Application\Service\NewsAggregatorService;
use App\News\Domain\Entity\NewsArticle;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use App\News\Domain\ValueObject\ArticleContent;
use App\News\Domain\ValueObject\ExternalId;
use App\News\Domain\ValueObject\Language;
use App\News\Domain\ValueObject\Source;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
final class NewsControllerTest extends WebTestCase
{
    /** @var NewsAggregatorService&MockObject */
    private NewsAggregatorService|MockObject $newsAggregatorService;
    /** @var NewsArticleRepositoryInterface&MockObject */
    private NewsArticleRepositoryInterface|MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->newsAggregatorService = $this->createMock(NewsAggregatorService::class);
        $this->repository = $this->createMock(NewsArticleRepositoryInterface::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(string $content): array
    {
        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            $this->fail('Failed to decode JSON response');
        }
        return $decoded;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function encodeJson(array $data): string
    {
        $encoded = json_encode($data);
        if ($encoded === false) {
            $this->fail('Failed to encode JSON');
        }
        return $encoded;
    }

    public function test_ingest_success(): void
    {
        $client = static::createClient();

        $this->newsAggregatorService
            ->expects($this->once())
            ->method('ingestArticles')
            ->willReturn([
                'saved' => 5,
                'updated' => 2,
                'skipped' => 3,
                'errors' => 0,
            ]);

        $client->getContainer()->set(NewsAggregatorService::class, $this->newsAggregatorService);

        $client->request('POST', '/api/news/ingest', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson([
            'keyword' => 'technology',
            'language' => 'en',
            'max' => 10,
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('saved', $response);
        $this->assertArrayHasKey('updated', $response);
        $this->assertArrayHasKey('skipped', $response);
        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals(5, $response['saved']);
        $this->assertEquals(2, $response['updated']);
        $this->assertEquals(3, $response['skipped']);
        $this->assertEquals(0, $response['errors']);
    }

    public function test_ingest_validation_error_missing_keyword_and_country(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/news/ingest', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson([
            'language' => 'en',
            'max' => 10,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('violations', $response);
        $this->assertEquals('Validation failed', $response['error']);
    }

    public function test_ingest_validation_error_invalid_language(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/news/ingest', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson([
            'keyword' => 'technology',
            'language' => 'invalid',
            'max' => 10,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('violations', $response);
        $this->assertEquals('Validation failed', $response['error']);

        $violation = $response['violations'][0];
        $this->assertEquals('language', $violation['field']);
        $this->assertStringContainsString('2-letter code', $violation['message']);
    }

    public function test_ingest_validation_error_max_out_of_range(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/news/ingest', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson([
            'keyword' => 'technology',
            'language' => 'en',
            'max' => 200,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('violations', $response);

        $violation = $response['violations'][0];
        $this->assertEquals('max', $violation['field']);
        $this->assertStringContainsString('between', $violation['message']);
    }

    public function test_ingest_validation_error_multiple_violations(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/news/ingest', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson([
            'language' => 'invalid',
            'max' => 500,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('violations', $response);
        $this->assertGreaterThanOrEqual(2, count($response['violations']));
    }

    public function test_list_success(): void
    {
        $client = static::createClient();

        $article = $this->createNewsArticle();

        $this->newsAggregatorService
            ->expects($this->once())
            ->method('getArticles')
            ->willReturn([
                'articles' => [$article],
                'total' => 1,
            ]);

        $client->getContainer()->set(NewsAggregatorService::class, $this->newsAggregatorService);

        $client->request('GET', '/api/news');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('page', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('total_pages', $response);

        $this->assertCount(1, $response['items']);
        $this->assertEquals(1, $response['total']);
        $this->assertEquals(1, $response['page']);
    }

    public function test_list_with_filters(): void
    {
        $client = static::createClient();

        $article = $this->createNewsArticle();

        $this->newsAggregatorService
            ->expects($this->once())
            ->method('getArticles')
            ->with(
                $this->callback(function ($filters) {
                    return isset($filters['keyword']) && $filters['keyword'] === 'AI';
                }),
                2,
                20
            )
            ->willReturn([
                'articles' => [$article],
                'total' => 1,
            ]);

        $client->getContainer()->set(NewsAggregatorService::class, $this->newsAggregatorService);

        $client->request('GET', '/api/news?keyword=AI&page=2&limit=20');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function test_list_validation_error_invalid_page(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/news?page=0');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Validation failed', $response['error']);
    }

    public function test_list_validation_error_invalid_limit(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/news?limit=200');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Validation failed', $response['error']);
    }

    public function test_show_success(): void
    {
        $client = static::createClient();

        $article = $this->createNewsArticle();

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($article);

        $client->getContainer()->set(NewsArticleRepositoryInterface::class, $this->repository);

        $client->request('GET', '/api/news/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('title', $response);
        $this->assertArrayHasKey('description', $response);
        $this->assertArrayHasKey('content', $response);
        $this->assertArrayHasKey('source_name', $response);
        $this->assertArrayHasKey('url', $response);
        $this->assertArrayHasKey('language', $response);
    }

    public function test_show_not_found(): void
    {
        $client = static::createClient();

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $client->getContainer()->set(NewsArticleRepositoryInterface::class, $this->repository);

        $client->request('GET', '/api/news/999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $response = $this->decodeJsonResponse((string) $client->getResponse()->getContent());

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Article not found', $response['error']);
    }

    private function createNewsArticle(): NewsArticle
    {
        return NewsArticle::reconstituteFromPersistence(
            id: 1,
            externalId: ExternalId::fromString('https://example.com/article-1'),
            content: ArticleContent::create(
                title: 'Test Article',
                description: 'Test Description',
                content: 'Test Content'
            ),
            source: Source::fromName('Test Source'),
            url: 'https://example.com/article-1',
            imageUrl: 'https://example.com/image.jpg',
            publishedAt: new \DateTimeImmutable('2024-12-01 10:00:00'),
            language: Language::fromCode('en'),
            createdAt: new \DateTimeImmutable('2024-12-01 10:00:00'),
            updatedAt: new \DateTimeImmutable('2024-12-01 10:00:00')
        );
    }
}

