<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Http\Controller;

use App\News\Application\DTO\IngestNewsRequest;
use App\News\Application\DTO\ListNewsRequest;
use App\News\Application\DTO\NewsArticleDTO;
use App\News\Application\DTO\PaginatedResponseDTO;
use App\News\Application\Service\NewsAggregatorService;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/news')]
final class NewsController extends AbstractController
{
    public function __construct(
        private readonly NewsAggregatorService $newsAggregatorService,
        private readonly NewsArticleRepositoryInterface $repository,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/ingest', name: 'news_ingest', methods: ['POST'])]
    public function ingest(
        #[MapRequestPayload] IngestNewsRequest $request,
    ): JsonResponse {
        try {
            $stats = $this->newsAggregatorService->ingestArticles($request->toApiParameters());

            return $this->json($stats, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Ingestion failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->json(
                [
                    'error' => 'Failed to ingest articles: ' . $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    #[Route('', name: 'news_list', methods: ['GET'])]
    public function list(
        #[MapQueryString] ListNewsRequest $request,
    ): JsonResponse {
        try {
            $result = $this->newsAggregatorService->getArticles(
                $request->toFilters(),
                $request->page,
                $request->limit
            );

            $dtos = array_map(
                static fn ($article) => NewsArticleDTO::fromDomainEntity($article),
                $result['articles'],
            );

            $response = new PaginatedResponseDTO(
                items: $dtos,
                total: $result['total'],
                page: $request->page,
                limit: $request->limit,
                totalPages: (int) ceil($result['total'] / $request->limit),
            );

            return $this->json($response);
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch articles', [
                'error' => $e->getMessage(),
            ]);

            return $this->json(
                [
                    'error' => 'Failed to fetch articles',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    #[Route('/{id}', name: 'news_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $article = $this->repository->findById($id);

            if ($article === null) {
                return $this->json(
                    [
                        'error' => 'Article not found',
                    ],
                    Response::HTTP_NOT_FOUND,
                );
            }

            $articleDto = NewsArticleDTO::fromDomainEntity($article);

            return $this->json($articleDto);
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch article', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->json(
                [
                    'error' => 'Failed to fetch article',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
