<?php

declare(strict_types = 1);

namespace Khalilthiero\RssFetcher\Updates;

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class CreateSourcesTable
 *
 * @package Khalilthiero\RssFetcher\Updates
 */
class CreateSourcesTable extends Migration {

    public function up() {
        Schema::create('khalilthiero_rssfetcher_sources', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 40)->nullable();
            $table->mediumText('description')->nullable();
            $table->mediumText('source_url');
            $table->smallInteger('max_items');
            $table->mediumText('category')->nullable();
            $table->dateTime('fetched_at')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down() {
        Schema::dropIfExists('khalilthiero_rssfetcher_sources');
    }

}
