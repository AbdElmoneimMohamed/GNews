<?php

declare(strict_types=1);

namespace App\News\Domain\ValueObject;

final readonly class ExternalId
{
    private function __construct(
        private string $value,
    ) {
        if (trim($this->value) === '') {
            throw new \InvalidArgumentException('External ID cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
