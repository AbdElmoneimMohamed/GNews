<?php

declare(strict_types=1);

namespace App\News\Domain\ValueObject;

final readonly class ArticleContent
{
    private function __construct(
        private string $title,
        private string $description,
        private string $content,
    ) {
        if (trim($this->title) === '') {
            throw new \InvalidArgumentException('Article title cannot be empty');
        }
    }

    public static function create(string $title, string $description, string $content): self
    {
        return new self($title, $description, $content);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function equals(self $other): bool
    {
        return $this->title === $other->title
            && $this->description === $other->description
            && $this->content === $other->content;
    }
}
