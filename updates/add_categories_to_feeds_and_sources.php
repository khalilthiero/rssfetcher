<?php

declare(strict_types = 1);

namespace Khalilthiero\RssFetcher\Updates;

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class AddEnclosureColumn
 *
 * @package Khalilthiero\RssFetcher\Updates
 */
class AddCategoriesToFeedsAndSources extends Migration {

    public function up() {
        Schema::create('khalilthiero_rssfetcher_rsscategories', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->index();
            $table->timestamps();
        });
        Schema::create('khalilthiero_rssfetcher_bpcategories_feeds', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('feed_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->primary(['feed_id', 'category_id'], 'khalilthiero_rssfetcher_bpcategories_feeds_pk');
        });
        Schema::create('khalilthiero_rssfetcher_rsscategories_feeds', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('feed_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->primary(['feed_id', 'category_id'], 'khalilthiero_rssfetcher_rsscategories_feeds_pk');
        });
        Schema::create('khalilthiero_rssfetcher_rsscategories_sources', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('source_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->primary(['source_id', 'category_id'], 'khalilthiero_rssfetcher_rsscategories_sources_pk');
        });
        Schema::table('khalilthiero_rssfetcher_sources', function (Blueprint $table) {
            $table->dropColumn([
                'category',
            ]);
        });
    }

    public function down() {
        
        Schema::drop('khalilthiero_rssfetcher_rsscategories');
        Schema::drop('khalilthiero_rssfetcher_bpcategories_feeds');
        Schema::drop('khalilthiero_rssfetcher_rsscategories_sources');
        Schema::drop('khalilthiero_rssfetcher_rsscategories_feeds');
        Schema::table('khalilthiero_rssfetcher_sources', function (Blueprint $table) {
            $table->mediumText('category')->nullable();
        });
    }

}
