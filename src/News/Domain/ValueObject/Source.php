<?php

declare(strict_types=1);

namespace App\News\Domain\ValueObject;

final readonly class Source
{
    private function __construct(
        private string $name,
    ) {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('Source name cannot be empty');
        }
    }

    public static function fromName(string $name): self
    {
        return new self($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }
}
