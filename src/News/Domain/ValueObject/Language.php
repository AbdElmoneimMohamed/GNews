<?php

declare(strict_types=1);

namespace App\News\Domain\ValueObject;

final readonly class Language
{
    private function __construct(
        private string $code,
    ) {
        if (! preg_match('/^[a-z]{2}$/', $this->code)) {
            throw new \InvalidArgumentException('Language code must be 2 lowercase letters');
        }
    }

    public static function fromCode(string $code): self
    {
        return new self(strtolower($code));
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }
}
