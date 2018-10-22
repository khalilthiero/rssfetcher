<?php

declare(strict_types = 1);

use Khalilthiero\RssFetcher\Models\Feed as FeedModel;
use Khalilthiero\RssFetcher\Models\Item;
use Khalilthiero\RssFetcher\Models\Source;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Zend\Feed\Exception\InvalidArgumentException;
use Zend\Feed\Writer\Entry;
use Zend\Feed\Writer\Feed;
use RainLab\Blog\Models\Post;
use System\Models\File;

Route::get('/feeds/{path}', function ($path) {

    /** @var FeedModel $model */
    $feedModel = FeedModel::where(['path' => $path, 'is_enabled' => 1])->first();
    if (is_null($feedModel)) {
        return Response::make('Not Found', 404);
    }

    $rssFeed = new Feed();
    $rssFeed->setTitle($feedModel->getAttribute('title'))
            ->setDescription($feedModel->getAttribute('description'))
            ->setBaseUrl(Url::to('/'))
            ->setGenerator('OctoberCMS/khalilthiero.RssFetcher')
            ->setId('khalilthiero.RssFecther.' . $feedModel->getAttribute('id'))
            ->setLink(Url::to('/feeds/' . $path))
            ->setFeedLink(Url::to('/feeds/' . $path), $feedModel->getAttribute('type'))
            ->setDateModified()
            ->addAuthor(['name' => 'OctoberCMS']);

    /** @var Collection $sources */
    $blogPostCategoriesIds = Arr::pluck($feedModel->bpcategories->toArray(), 'id');
    $blogPostClass = 'RainLab\\Blog\\Models\\Post';
    $posts = $blogPostClass::where('published', '=', 1)
            ->whereDate('published_at', '<=', date('Y-m-d'))
            ->whereHas('categories', function($query)use ($blogPostCategoriesIds) {
                $query->whereIn('rainlab_blog_categories.id', $blogPostCategoriesIds);
            })
            ->orderBy('published_at', 'desc')
            ->limit($feedModel->getAttribute('max_items'))
            ->get();
    /** @var Post $post */
    foreach ($posts as $post) {
        try {
            $entry = new Entry();
            $postLink = url($post->getTranslateAttribute('slug',$feedModel->lang));
            $description = $post->getTranslateAttribute('content',$feedModel->lang);
            $entry->setId((string) $post->getAttribute('id'))
                    ->setTitle($post->getTranslateAttribute('title',$feedModel->lang))
                    ->setDescription($description)
                    ->setLink($postLink)
                    ->setDateModified($post->getAttribute('published_at'));
//            $comments = $post->getAttribute('comments');
            $comments = null;
            if (!empty($comments)) {
                $entry->setCommentLink($comments);
            }

            $blogPostcategories = $post->categories;

            if (!empty($blogPostcategories)) {
                foreach ($blogPostcategories as $category) {
                    $entry->addCategory(['term' => $category->name]);
                }
            }

            $enclosureUrl = $post->featured_images()->first();
            if (!empty($enclosureUrl)) {
                $entry->setEnclosure([
                    'uri' => $enclosureUrl->getPath(),
                    'type' => $enclosureUrl->getContentType(),
                    'length' => 0,
                ]);
            }

            $rssFeed->addEntry($entry);
        } catch (InvalidArgumentException $e) {
            continue;
        }
    }

    $rssCategoriesIds = Arr::pluck($feedModel->rsscategories->toArray(), 'id');
    $items = Item::where('is_published', '=', 1)
            ->whereDate('pub_date', '<=', date('Y-m-d'))
            ->whereHas('source.rsscategories', function($query)use ($rssCategoriesIds) {
                $query->whereIn('khalilthiero_rssfetcher_rsscategories_sources.category_id', $rssCategoriesIds);
            })
            ->with('source')
            ->orderBy('pub_date', 'desc')
            ->limit($feedModel->getAttribute('max_items'))
            ->get();

    /** @var Item $item */
    foreach ($items as $item) {
        try {
            $entry = new Entry();
            $itemLink = $item->getAttribute('link');
            $description = $item->getAttribute('description');
            $entry->setId((string) $item->getAttribute('item_id'))
                    ->setTitle($item->getAttribute('title'))
                    ->setDescription($description)
                    ->setLink($itemLink)
                    ->setDateModified($item->getAttribute('pub_date'));

//            $comments = $item->getAttribute('comments');
            $comments = null;
            if (!empty($comments)) {
                $entry->setCommentLink($comments);
            }

            $rssCategories = $item->source->rsscategories;

            if (!empty($rssCategories)) {
                foreach ($rssCategories as $category) {
                    $entry->addCategory(['term' => $category->name]);
                }
            }
            if (stripos($item->enclosure_type, 'image') !== false) {
                $entry->setEnclosure([
                    'uri' => $item->enclosure_url,
                    'type' => $item->enclosure_type,
                    'length' => $item->enclosure_length,
                ]);
            }

            $rssFeed->addEntry($entry);
        } catch (InvalidArgumentException $e) {
            continue;
        }
    }
    return Response::make($rssFeed->export($feedModel->getAttribute('type')), 200, [
                'Content-Type' => sprintf('application/%s+xml', $feedModel->getAttribute('type')),
    ]);
});
