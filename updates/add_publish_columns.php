<?php

declare(strict_types=1);

namespace Khalilthiero\RssFetcher\Updates;

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class AddPublishColumns
 *
 * @package Khalilthiero\RssFetcher\Updates
 */
class AddPublishColumns extends Migration
{
    public function up()
    {
        Schema::table('khalilthiero_rssfetcher_items', function (Blueprint $table) {
            $table->boolean('is_published')->default(true);
        });

        Schema::table('khalilthiero_rssfetcher_sources', function (Blueprint $table) {
            $table->boolean('publish_new_items')->default(true);
        });
    }

    public function down()
    {
        Schema::table('khalilthiero_rssfetcher_items', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        Schema::table('khalilthiero_rssfetcher_sources', function (Blueprint $table) {
            $table->dropColumn('publish_new_items');
        });
    }
}
