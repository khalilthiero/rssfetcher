<?php

declare(strict_types=1);

namespace Khalilthiero\RssFetcher\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use BackendMenu;
use Backend\Classes\Controller;

/**
 * Class Feeds
 *
 * @package Khalilthiero\RssFetcher\Controllers
 * @mixin FormController
 * @mixin ListController
 */
class Feeds extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    /** @var string */
    public $formConfig = 'config_form.yaml';

    /** @var string */
    public $listConfig = 'config_list.yaml';

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('khalilthiero.RssFetcher', 'rssfetcher', 'feeds');
    }
}
