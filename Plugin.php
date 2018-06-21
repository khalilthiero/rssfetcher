<?php

declare(strict_types = 1);

namespace Khalilthiero\RssFetcher;

use Backend;
use System\Classes\PluginBase;

/**
 * Class Plugin
 *
 * @package Khalilthiero\RssFetcher
 */
class Plugin extends PluginBase {

    /**
     * {@inheritdoc}
     */
    public function pluginDetails(): array {
        return [
            'name' => 'khalilthiero.rssfetcher::lang.plugin.name',
            'description' => 'khalilthiero.rssfetcher::lang.plugin.name',
            'author' => 'A. Drenth <khalilthiero@gmail.com>',
            'icon' => 'icon-rss',
            'homepage' => 'http://github.com/khalilthiero/rssfetcher'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function register() {
        $this->registerConsoleCommand(
                'khalilthiero.RssFetcher', Commands\FetchRssCommand::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function registerComponents(): array {
        return [
            Components\Items::class => 'rssItems',
            Components\PaginatableItems::class => 'rssPaginatableItems',
            Components\Sources::class => 'rssSources'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerReportWidgets(): array {
        return [
            ReportWidgets\Headlines::class => [
                'label' => 'RSS Headlines',
                'code' => 'headlines'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerPermissions(): array {
        return [
            'khalilthiero.rssfetcher.access_sources' => [
                'tab' => 'khalilthiero.rssfetcher::lang.plugin.name',
                'label' => 'khalilthiero.rssfetcher::lang.permissions.access_sources'
            ],
            'khalilthiero.rssfetcher.access_items' => [
                'tab' => 'khalilthiero.rssfetcher::lang.plugin.name',
                'label' => 'khalilthiero.rssfetcher::lang.permissions.access_items'
            ],
            'khalilthiero.rssfetcher.access_import_export' => [
                'tab' => 'khalilthiero.rssfetcher::lang.plugin.name',
                'label' => 'khalilthiero.rssfetcher::lang.permissions.access_import_export'
            ],
            'khalilthiero.rssfetcher.access_feeds' => [
                'tab' => 'khalilthiero.rssfetcher::lang.plugin.name',
                'label' => 'khalilthiero.rssfetcher::lang.permissions.access_feeds'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerNavigation(): array {
        return [
            'rssfetcher' => [
                'label' => 'khalilthiero.rssfetcher::lang.navigation.menu_label',
                'url' => Backend::url('khalilthiero/rssfetcher/sources'),
                'icon' => 'icon-rss',
//                'permissions' => ['khalilthiero.rssfetcher.*'],
                'order' => 500,
                'sideMenu' => [
                    'sources' => [
                        'label' => 'khalilthiero.rssfetcher::lang.navigation.side_menu_label_sources',
                        'icon' => 'icon-globe',
                        'url' => Backend::url('khalilthiero/rssfetcher/sources'),
                        'permissions' => ['khalilthiero.rssfetcher.access_sources']
                    ],
                    'items' => [
                        'label' => 'khalilthiero.rssfetcher::lang.navigation.side_menu_label_items',
                        'icon' => 'icon-files-o',
                        'url' => Backend::url('khalilthiero/rssfetcher/items'),
                        'permissions' => ['khalilthiero.rssfetcher.access_items']
                    ],
                    'feeds' => [
                        'label' => 'khalilthiero.rssfetcher::lang.navigation.side_menu_label_feeds',
                        'icon' => 'icon-rss',
                        'url' => Backend::url('khalilthiero/rssfetcher/feeds'),
                        'permissions' => ['khalilthiero.rssfetcher.access_feeds']
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerFormWidgets(): array {
        return [
            FormWidgets\TextWithPrefix::class => 'textWithPrefix'
        ];
    }

}
