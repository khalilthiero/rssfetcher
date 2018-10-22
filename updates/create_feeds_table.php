<?php

declare(strict_types = 1);

namespace Khalilthiero\RssFetcher\Updates;

use Illuminate\Database\Schema\Blueprint;
use Schema;
use October\Rain\Database\Updates\Migration;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class CreateFeedsTable
 *
 * @package Khalilthiero\RssFetcher\Updates
 */
class CreateFeedsTable extends Migration {

    public function up() {
        Schema::create('khalilthiero_rssfetcher_feeds', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('type');
            $table->string('title');
            $table->string('description');
            $table->string('path', 191)->unique('feeds_path_unique');
            $table->mediumText('category')->nullable();
            $table->unsignedTinyInteger('max_items');
            $table->boolean('is_enabled');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('khalilthiero_rssfetcher_feeds');
    }

}
