<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Persistence\Doctrine\Entity;

use App\News\Infrastructure\Persistence\Doctrine\Repository\NewsArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsArticleRepository::class)]
#[ORM\Table(name: 'news_articles')]
#[ORM\Index(columns: ['external_id'], name: 'idx_external_id')]
#[ORM\Index(columns: ['published_at'], name: 'idx_published_at')]
#[ORM\Index(columns: ['source_name'], name: 'idx_source_name')]
class NewsArticleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $externalId;

    #[ORM\Column(type: Types::STRING, length: 500)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $sourceName;

    #[ORM\Column(type: Types::STRING, length: 500)]
    private string $url;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $publishedAt;

    #[ORM\Column(type: Types::STRING, length: 2)]
    private string $language;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    public function setSourceName(string $sourceName): self
    {
        $this->sourceName = $sourceName;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
