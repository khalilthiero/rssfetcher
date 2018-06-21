<?php

declare(strict_types=1);

namespace Khalilthiero\RssFetcher\Commands;

use Khalilthiero\RssFetcher\Classes\RssFetcher;
use Exception;
use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class FetchRssCommand
 *
 * @package Khalilthiero\RssFetcher\Commands
 */
class FetchRssCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'khalilthiero:fetch-rss';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Fetches RSS data from various sources.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws RuntimeException
     * @throws Exception
     */
    public function handle()
    {
        $sourceId = (int) $this->argument('source');

        RssFetcher::instance()->fetch($sourceId > 0 ? $sourceId : null);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */

    /**
     * {@inheritdoc}
     */
    protected function getArguments(): array
    {
        return [
            ['source', InputArgument::OPTIONAL, 'Source ID'],
        ];
    }
}
