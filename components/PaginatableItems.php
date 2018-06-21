<?php

declare(strict_types=1);

namespace Khalilthiero\RssFetcher\Components;

use Khalilthiero\RssFetcher\Models\Item;
use Cms\Classes\ComponentBase;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Class PaginatableItems
 *
 * @package Khalilthiero\RssFetcher\Components
 */
class PaginatableItems extends ComponentBase
{
    /**
     * @var LengthAwarePaginator
     */
    public $items;

    /**
     * {@inheritdoc}
     */
    public function componentDetails()
    {
        return [
            'name' => 'khalilthiero.rssfetcher::lang.component.paginatable_item_list.name',
            'description' => 'khalilthiero.rssfetcher::lang.component.paginatable_item_list.description'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function defineProperties(): array
    {
        return [
            'itemsPerPage' => [
                'title' => 'khalilthiero.rssfetcher::lang.item.items_per_page',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'khalilthiero.rssfetcher::lang.item.items_per_page_validation',
                'default' => '10',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function onRun()
    {
        $this->items = $this->loadItems();
    }

    /**
     * Load Items
     *
     * @return LengthAwarePaginator|array
     */
    protected function loadItems()
    {
        try {
            $items = Item::select(['khalilthiero_rssfetcher_items.*', 'khalilthiero_rssfetcher_sources.name AS source'])
                 ->join(
                     'khalilthiero_rssfetcher_sources',
                     'khalilthiero_rssfetcher_items.source_id',
                     '=',
                     'khalilthiero_rssfetcher_sources.id'
                 )
                 ->where('khalilthiero_rssfetcher_sources.is_enabled', '=', 1)
                 ->where('khalilthiero_rssfetcher_items.is_published', '=', 1)
                 ->orderBy('khalilthiero_rssfetcher_items.pub_date', 'desc')
                 ->paginate($this->property('itemsPerPage'));
        } catch (InvalidArgumentException $e) {
            return [];
        }

        return $items;
    }
}
