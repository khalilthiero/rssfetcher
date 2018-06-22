<?php

declare(strict_types=1);

namespace Khalilthiero\RssFetcher\Models;

use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Class Feed
 *
 * @package Khalilthiero\RssFetcher\Models
 */
class Feed extends Model
{
    use Validation;

    /**
     * {@inheritdoc}
     */
    public $table = 'khalilthiero_rssfetcher_feeds';

    /**
     * {@inheritdoc}
     */
    public $belongsToMany = [
        'categories' => [
            'RainLab\Blog\Models\Category',
            'table' => 'khalilthiero_rssfetcher_categories_feeds',
            'order' => 'name'
        ]
    ];

    /** @var array */
    public $rules = [
        'title' => 'required',
        'description' => 'required',
        'path' => [
            'required',
            'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i',
            'unique:khalilthiero_rssfetcher_feeds'
        ],
        'type' => 'required'
    ];
}
