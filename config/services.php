<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\News\Application\Service\NewsAggregatorService;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use App\News\Infrastructure\ExternalApi\GNews\GNewsApiService;
use App\News\Infrastructure\ExternalApi\GNews\GNewsApiServiceInterface;
use App\News\Infrastructure\Persistence\Doctrine\Repository\NewsArticleRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->load('App\\News\\', '../src/News/')
        ->exclude([
            '../src/News/Domain/Entity/',
            '../src/News/Domain/ValueObject/',
            '../src/News/Application/DTO/',
            '../src/News/Infrastructure/Persistence/Doctrine/Entity/',
            '../src/News/Infrastructure/Http/Exception/',
        ])
    ;

    $services->set(NewsArticleRepositoryInterface::class)
        ->class(NewsArticleRepository::class)
    ;

    $services->set(GNewsApiServiceInterface::class)
        ->class(GNewsApiService::class)
        ->arg('$apiKey', env('GNEWS_API_KEY'))
        ->arg('$baseUrl', env('GNEWS_API_BASE_URL'))
    ;

    $services->set(NewsAggregatorService::class);
};
