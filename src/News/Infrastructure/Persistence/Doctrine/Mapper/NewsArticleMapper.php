<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Persistence\Doctrine\Mapper;

use App\News\Domain\Entity\NewsArticle;
use App\News\Domain\ValueObject\ArticleContent;
use App\News\Domain\ValueObject\ExternalId;
use App\News\Domain\ValueObject\Language;
use App\News\Domain\ValueObject\Source;
use App\News\Infrastructure\Persistence\Doctrine\Entity\NewsArticleEntity;

final class NewsArticleMapper
{
    public static function mapArticalEntityToArticalModel(NewsArticleEntity $entity): NewsArticle
    {
        $id = $entity->getId();
        if ($id === null) {
            throw new \RuntimeException('Cannot map entity without ID to domain');
        }

        return NewsArticle::reconstituteFromPersistence(
            id: $id,
            externalId: ExternalId::fromString($entity->getExternalId()),
            content: ArticleContent::create(
                title: $entity->getTitle(),
                description: $entity->getDescription(),
                content: $entity->getContent(),
            ),
            source: Source::fromName($entity->getSourceName()),
            url: $entity->getUrl(),
            imageUrl: $entity->getImageUrl(),
            publishedAt: $entity->getPublishedAt(),
            language: Language::fromCode($entity->getLanguage()),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
        );
    }

    public static function mapArticalModelToArticalEntity(NewsArticle $domain): NewsArticleEntity
    {
        $entity = new NewsArticleEntity();

        $entity
            ->setExternalId($domain->getExternalId()->toString())
            ->setTitle($domain->getContent()->getTitle())
            ->setDescription($domain->getContent()->getDescription())
            ->setContent($domain->getContent()->getContent())
            ->setSourceName($domain->getSource()->getName())
            ->setUrl($domain->getUrl())
            ->setImageUrl($domain->getImageUrl())
            ->setPublishedAt($domain->getPublishedAt())
            ->setLanguage($domain->getLanguage()->getCode())
            ->setCreatedAt($domain->getCreatedAt())
            ->setUpdatedAt($domain->getUpdatedAt())
        ;

        return $entity;
    }

    public static function syncEntityFromDomainModel(NewsArticleEntity $entity, NewsArticle $domain): void
    {
        $entity
            ->setTitle($domain->getContent()->getTitle())
            ->setDescription($domain->getContent()->getDescription())
            ->setContent($domain->getContent()->getContent())
            ->setSourceName($domain->getSource()->getName())
            ->setUrl($domain->getUrl())
            ->setImageUrl($domain->getImageUrl())
            ->setPublishedAt($domain->getPublishedAt())
            ->setLanguage($domain->getLanguage()->getCode())
            ->setUpdatedAt($domain->getUpdatedAt())
        ;
    }
}
