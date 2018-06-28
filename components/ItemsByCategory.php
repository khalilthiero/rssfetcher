<?php

declare(strict_types = 1);

namespace Khalilthiero\RssFetcher\Components;

use Khalilthiero\RssFetcher\Models\Item;
use Cms\Classes\ComponentBase;
use InvalidArgumentException;
use October\Rain\Support\Collection;

/**
 * Class Items
 *
 * @package Khalilthiero\RssFetcher\Components
 */
class ItemsByCategory extends ComponentBase {

    /**
     * @var Collection
     */
    public $items;

    /**
     * {@inheritdoc}
     */
    public function componentDetails() {
        return [
            'name' => 'khalilthiero.rssfetcher::lang.component.item_by_category.name',
            'description' => 'khalilthiero.rssfetcher::lang.component.item_by_category.description'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function defineProperties(): array {
        return [
            'maxItems' => [
                'label' => 'khalilthiero.rssfetcher::lang.item.max_items',
                'type' => 'string',
                'default' => '10'
            ],
            'category' => [
                'label' => 'khalilthiero.rssfetcher::lang.item.rsscategory',
                'type' => 'string',
                'default' => ''
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function onRun() {
        $category = (string) $this->property('category');

        $this->items = self::loadItems((int) $this->property('maxItems', 10), $category);
    }

    /**
     * Load Items
     *
     * @param int $maxItems
     * @param int $category
     * @return array
     */
    public static function loadItems(int $maxItems, string $category): \Illuminate\Pagination\LengthAwarePaginator {
        try {
            $items = Item::select(['khalilthiero_rssfetcher_items.*', 'khalilthiero_rssfetcher_sources.name AS source'])
                    ->join(
                            'khalilthiero_rssfetcher_sources', 'khalilthiero_rssfetcher_items.source_id', '=', 'khalilthiero_rssfetcher_sources.id'
                    )
                    ->where('khalilthiero_rssfetcher_sources.is_enabled', '=', 1)
                    ->where('khalilthiero_rssfetcher_items.is_published', '=', 1)
                    ->orderBy('khalilthiero_rssfetcher_items.pub_date', 'desc')
                    ->join('khalilthiero_rssfetcher_rsscategories_sources','khalilthiero_rssfetcher_sources.id','=','khalilthiero_rssfetcher_rsscategories_sources.source_id')
                    ->join('khalilthiero_rssfetcher_rsscategories','khalilthiero_rssfetcher_rsscategories.id','=','khalilthiero_rssfetcher_rsscategories_sources.category_id')
                    ->where('khalilthiero_rssfetcher_rsscategories.slug', '=', $category)
                    ->paginate($maxItems);
        } catch (InvalidArgumentException $e) {
            return [];
        }
        return $items;
    }

}
