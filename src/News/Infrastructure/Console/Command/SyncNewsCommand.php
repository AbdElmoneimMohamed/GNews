<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Console\Command;

use App\News\Application\Service\NewsAggregatorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:news:sync',
    description: 'Synchronize news articles from GNews API',
)]
class SyncNewsCommand extends Command
{
    public function __construct(
        private readonly NewsAggregatorService $newsAggregatorService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('keyword', 'k', InputOption::VALUE_REQUIRED, 'Search keyword')
            ->addOption('language', 'l', InputOption::VALUE_OPTIONAL, 'Language code (e.g., en, es)', 'en')
            ->addOption('country', 'c', InputOption::VALUE_OPTIONAL, 'Country code (e.g., us, uk)')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'Start date (ISO 8601)')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'End date (ISO 8601)')
            ->addOption('max', 'm', InputOption::VALUE_OPTIONAL, 'Maximum articles to fetch', '10')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parameters = $this->buildParameters($input);

        if (empty($parameters['q']) && empty($parameters['country'])) {
            return Command::FAILURE;
        }
        try {
            $this->newsAggregatorService->ingestArticles($parameters);

            return Command::SUCCESS;
        } catch (\Exception) {
            return Command::FAILURE;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildParameters(InputInterface $input): array
    {
        $parameters = [];

        if ($input->getOption('keyword')) {
            $parameters['q'] = $input->getOption('keyword');
        }

        if ($input->getOption('language')) {
            $parameters['lang'] = $input->getOption('language');
        }

        if ($input->getOption('country')) {
            $parameters['country'] = $input->getOption('country');
        }

        if ($input->getOption('from')) {
            $parameters['from'] = $input->getOption('from');
        }

        if ($input->getOption('to')) {
            $parameters['to'] = $input->getOption('to');
        }

        if ($input->getOption('max')) {
            $parameters['max'] = (int) $input->getOption('max');
        }

        return $parameters;
    }
}
