<?php

declare(strict_types=1);

namespace Khalilthiero\RssFetcher\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * Class TextWithPrefix
 *
 * @package Khalilthiero\RssFetcher\FormWidgets
 */
class TextWithPrefix extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'khalilthiero_rssfetcher_text_with_prefix';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->formField->config['prefix'] = '/feeds/';
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('textwithprefix');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
    }
}
