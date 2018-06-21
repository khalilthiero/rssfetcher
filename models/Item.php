<?php

declare(strict_types=1);

namespace Khalilthiero\RssFetcher\Models;

use Model;
use October\Rain\Database\Builder;

/**
 * Class Item
 *
 * @package Khalilthiero\RssFetcher\Models
 */
class Item extends Model
{
    /**
     * {@inheritdoc}
     */
    public $table = 'khalilthiero_rssfetcher_items';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'source_id',
        'item_id',
        'post_id',
        'title',
        'link',
        'description',
        'author',
        'tags',
        'comments',
        'enclosure_url',
        'enclosure_length',
        'enclosure_type',
        'pub_date',
        'is_published'
    ];

    /**
     * {@inheritdoc}
     */
    protected $dates = [
        'pub_date'
    ];

    /**
     * {@inheritdoc}
     */
    public $belongsTo = [
        'source' => Source::class
    ];

    /**
     * Allows filtering for specifc sources
     *
     * @param Builder $query
     * @param array $sources List of source ids
     * @return Builder
     */
    public function scopeFilterSources(Builder $query, array $sources = []): Builder
    {
        return $query->whereHas('source', function ($q) use ($sources) {
            $q->whereIn('id', $sources);
        });
    }
}
