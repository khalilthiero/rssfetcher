<?php

declare(strict_types = 1);

namespace Khalilthiero\RssFetcher\Updates;

use Khalilthiero\RssFetcher\Models\Category;
use October\Rain\Database\Updates\Seeder;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class SeedAllTables
 *
 * @package Khalilthiero\RssFetcher\Updates
 */
class SeedAllTables extends Seeder {

    /**
     * {@inheritdoc}
     */
    public function run() {
        $categories = ['North Africa','West Africa','Central Africa','East Africa','Southern Africa','Indian Ocean'];
        foreach ($categories as $value) {
            Category::create(['name' => $value]);
        }
    }

}
