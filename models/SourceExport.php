<?php

declare(strict_types=1);

namespace Khalilthiero\RssFetcher\Models;

use Backend\Models\ExportModel;

/**
 * Class SourceExport
 *
 * @package Khalilthiero\RssFetcher\Models
 */
class SourceExport extends ExportModel
{
    /**
     * {@inheritdoc}
     */
    public $table = 'khalilthiero_rssfetcher_sources';

    /**
     * {@inheritdoc}
     */
    public function exportData($columns, $sessionKey = null)
    {
        return self::make()->get()->toArray();
    }
}
