<?php

declare(strict_types=1);

namespace App\News\Domain\Entity;

use App\News\Domain\ValueObject\ArticleContent;
use App\News\Domain\ValueObject\ExternalId;
use App\News\Domain\ValueObject\Language;
use App\News\Domain\ValueObject\Source;

final class NewsArticle
{
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        private ?int $id,
        private readonly ExternalId $externalId,
        private ArticleContent $content,
        private Source $source,
        private string $url,
        private ?string $imageUrl,
        private \DateTimeImmutable $publishedAt,
        private Language $language,
        private readonly \DateTimeImmutable $createdAt,
    ) {
        $this->updatedAt = $createdAt;
    }

    public static function create(
        ExternalId $externalId,
        ArticleContent $content,
        Source $source,
        string $url,
        ?string $imageUrl,
        \DateTimeImmutable $publishedAt,
        Language $language,
    ): self {
        return new self(
            id: null,
            externalId: $externalId,
            content: $content,
            source: $source,
            url: $url,
            imageUrl: $imageUrl,
            publishedAt: $publishedAt,
            language: $language,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstituteFromPersistence(
        int $id,
        ExternalId $externalId,
        ArticleContent $content,
        Source $source,
        string $url,
        ?string $imageUrl,
        \DateTimeImmutable $publishedAt,
        Language $language,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        $article = new self(
            id: $id,
            externalId: $externalId,
            content: $content,
            source: $source,
            url: $url,
            imageUrl: $imageUrl,
            publishedAt: $publishedAt,
            language: $language,
            createdAt: $createdAt,
        );
        $article->updatedAt = $updatedAt;

        return $article;
    }

    public function updateContent(ArticleContent $newContent): void
    {
        if (! $this->content->equals($newContent)) {
            $this->content = $newContent;
            $this->markAsUpdated();
        }
    }

    public function updateSource(Source $newSource): void
    {
        if (! $this->source->equals($newSource)) {
            $this->source = $newSource;
            $this->markAsUpdated();
        }
    }

    public function updateUrl(string $newUrl): void
    {
        if ($this->url !== $newUrl) {
            $this->url = $newUrl;
            $this->markAsUpdated();
        }
    }

    public function updateImageUrl(?string $newImageUrl): void
    {
        if ($this->imageUrl !== $newImageUrl) {
            $this->imageUrl = $newImageUrl;
            $this->markAsUpdated();
        }
    }

    public function updatePublishedAt(\DateTimeImmutable $newPublishedAt): void
    {
        if ($this->publishedAt !== $newPublishedAt) {
            $this->publishedAt = $newPublishedAt;
            $this->markAsUpdated();
        }
    }

    public function hasContentChanged(ArticleContent $otherContent, \DateTimeImmutable $otherPublishedAt): bool
    {
        return ! $this->content->equals($otherContent) || $this->publishedAt !== $otherPublishedAt;
    }

    private function markAsUpdated(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalId(): ExternalId
    {
        return $this->externalId;
    }

    public function getContent(): ArticleContent
    {
        return $this->content;
    }

    public function getSource(): Source
    {
        return $this->source;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
