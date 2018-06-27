<?php

namespace Khalilthiero\RssFetcher\Models;

use Str;
use Model;
use October\Rain\Database\Traits\Validation;

class Category extends Model {

    use Validation;

    public $table = 'khalilthiero_rssfetcher_rsscategories';
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    /*
     * Validation
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required|between:3,64|unique:khalilthiero_rssfetcher_rsscategories',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'name',
        ['slug', 'index' => true]
    ];

    public function beforeValidate() {
        // Generate a URL slug for this model
        if (!$this->exists && !$this->slug) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function afterDelete() {
        $this->posts()->detach();
    }

}
