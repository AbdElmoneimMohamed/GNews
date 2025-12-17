<?php

declare(strict_types=1);

namespace App\News\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class ListNewsRequest
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public ?string $keyword = null,

        #[Assert\Regex(pattern: '/^[a-z]{2}$/', message: 'Language must be a 2-letter code')]
        public ?string $language = null,

        #[Assert\Length(max: 255)]
        public ?string $source = null,

        #[Assert\DateTime(format: 'Y-m-d', message: 'Invalid from date format')]
        public ?string $from = null,

        #[Assert\DateTime(format: 'Y-m-d', message: 'Invalid to date format')]
        public ?string $to = null,

        #[Assert\Range(min: 1, max: 1000, notInRangeMessage: 'Page must be between {{ min }} and {{ max }}')]
        public int $page = 1,

        #[Assert\Range(min: 1, max: 100, notInRangeMessage: 'Limit must be between {{ min }} and {{ max }}')]
        public int $limit = 10,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toFilters(): array
    {
        $filters = [];

        if ($this->keyword !== null) {
            $filters['keyword'] = $this->keyword;
        }

        if ($this->language !== null) {
            $filters['language'] = $this->language;
        }

        if ($this->source !== null) {
            $filters['source'] = $this->source;
        }

        if ($this->from !== null) {
            $filters['from'] = $this->from;
        }

        if ($this->to !== null) {
            $filters['to'] = $this->to;
        }

        return $filters;
    }
}
