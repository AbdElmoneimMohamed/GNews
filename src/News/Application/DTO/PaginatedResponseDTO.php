<?php

declare(strict_types=1);

namespace App\News\Application\DTO;

final readonly class PaginatedResponseDTO implements \JsonSerializable
{
    /**
     * @param array<NewsArticleDTO> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
        public int $totalPages,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
            'total_pages' => $this->totalPages,
        ];
    }
}
