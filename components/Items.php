<?php

declare(strict_types=1);

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
class Items extends ComponentBase
{
    /**
     * @var Collection
     */
    public $items;

    /**
     * {@inheritdoc}
     */
    public function componentDetails()
    {
        return [
            'name' => 'khalilthiero.rssfetcher::lang.component.item_list.name',
            'description' => 'khalilthiero.rssfetcher::lang.component.item_list.description'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function defineProperties(): array
    {
        return [
            'maxItems' => [
                'label' => 'khalilthiero.rssfetcher::lang.item.max_items',
                'type' => 'string',
                'default' => '10'
            ],
            'sourceId' => [
                'label' => 'khalilthiero.rssfetcher::lang.item.source_id',
                'type' => 'string',
                'default' => ''
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function onRun()
    {
        $sourceId = (int) $this->property('sourceId');

        $this->items = self::loadItems(
            (int) $this->property('maxItems', 10),
            $sourceId > 0 ? $sourceId : null
        );
    }

    /**
     * Load Items
     *
     * @param int $maxItems
     * @param int $sourceId
     * @return array
     */
    public static function loadItems(int $maxItems, int $sourceId = null): array
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
                ->limit($maxItems);

            if ($sourceId !== null && is_numeric($sourceId)) {
                $items->where('khalilthiero_rssfetcher_items.source_id', '=', (int) $sourceId);
            }
        } catch (InvalidArgumentException $e) {
            return [];
        }

        return $items->get()->toArray();
    }
}
