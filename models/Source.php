<?php

declare(strict_types=1);

namespace Khalilthiero\RssFetcher\Models;

use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Source Model
 */
class Source extends Model
{
    use Validation;

    /**
     * {@inheritdoc}
     */
    public $table = 'khalilthiero_rssfetcher_sources';

    /**
     * {@inheritdoc}
     */
    protected $dates = [
        'fetched_at'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public $rules = [
        'name' => 'required',
        'source_url' => 'required',
    ];

    /**
     * {@inheritdoc}
     */
    public $hasMany = [
        'items' => [
            Item::class,
        ],
        'items_count' => [
            Item::class,
            'count' => true
        ]
    ];
     /**
     * {@inheritdoc}
     */
    public $belongsToMany = [
        'rsscategories' => [
            'Khalilthiero\RssFetcher\Models\Category',
            'table' => 'khalilthiero_rssfetcher_rsscategories_sources',
            'order' => 'name'
        ]
    ];
}
