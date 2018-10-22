<?php namespace Khalilthiero\RssFetcher\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateKhalilthieroRssfetcherFeeds extends Migration
{
    public function up()
    {
        Schema::table('khalilthiero_rssfetcher_feeds', function($table)
        {
            $table->string('lang', 2)->default('en');
            $table->boolean('max_items')->unsigned(false)->change();
        });
    }
    
    public function down()
    {
        Schema::table('khalilthiero_rssfetcher_feeds', function($table)
        {
            $table->dropColumn('lang');
            $table->boolean('max_items')->unsigned()->change();
        });
    }
}
