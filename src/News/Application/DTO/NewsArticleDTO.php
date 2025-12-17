<?php

declare(strict_types=1);

namespace App\News\Application\DTO;

use App\News\Domain\Entity\NewsArticle;

final readonly class NewsArticleDTO implements \JsonSerializable
{
    public function __construct(
        public ?int $id,
        public string $externalId,
        public string $title,
        public string $description,
        public string $content,
        public string $sourceName,
        public string $url,
        public ?string $imageUrl,
        public string $publishedAt,
        public string $language,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromDomainEntity(NewsArticle $article): self
    {
        return new self(
            id: $article->getId(),
            externalId: $article->getExternalId()->toString(),
            title: $article->getContent()->getTitle(),
            description: $article->getContent()->getDescription(),
            content: $article->getContent()->getContent(),
            sourceName: $article->getSource()->getName(),
            url: $article->getUrl(),
            imageUrl: $article->getImageUrl(),
            publishedAt: $article->getPublishedAt()->format(\DateTimeInterface::ATOM),
            language: $article->getLanguage()->getCode(),
            createdAt: $article->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $article->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->externalId,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'source_name' => $this->sourceName,
            'url' => $this->url,
            'image_url' => $this->imageUrl,
            'published_at' => $this->publishedAt,
            'language' => $this->language,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
