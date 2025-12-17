<?php

declare(strict_types=1);

namespace App\News\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class IngestNewsRequest
{
    public function __construct(
        #[Assert\Expression(
            'this.keyword !== null or this.country !== null',
            message: 'Either keyword or country parameter is required'
        )]
        #[Assert\Length(max: 255)]
        public ?string $keyword = null,

        #[Assert\Regex(pattern: '/^[a-z]{2}$/', message: 'Language must be a 2-letter code')]
        public ?string $language = null,

        #[Assert\Regex(pattern: '/^[a-z]{2}$/', message: 'Country must be a 2-letter code')]
        public ?string $country = null,

        #[Assert\DateTime(format: 'Y-m-d', message: 'Invalid from date format')]
        public ?string $from = null,

        #[Assert\DateTime(format: 'Y-m-d', message: 'Invalid to date format')]
        public ?string $to = null,

        #[Assert\Range(min: 1, max: 100, notInRangeMessage: 'Max parameter must be between {{ min }} and {{ max }}')]
        public ?int $max = 10,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiParameters(): array
    {
        $parameters = [];

        if ($this->keyword !== null) {
            $parameters['q'] = $this->keyword;
        }

        if ($this->language !== null) {
            $parameters['lang'] = $this->language;
        }

        if ($this->country !== null) {
            $parameters['country'] = $this->country;
        }

        if ($this->from !== null) {
            $parameters['from'] = $this->from;
        }

        if ($this->to !== null) {
            $parameters['to'] = $this->to;
        }

        if ($this->max !== null) {
            $parameters['max'] = $this->max;
        }

        return $parameters;
    }
}
