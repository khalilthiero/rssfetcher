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
    $model = FeedModel::where(['path' => $path, 'is_enabled' => 1])->first();
    if (is_null($model)) {
        return Response::make('Not Found', 404);
    }

    $feed = new Feed();
    $feed->setTitle($model->getAttribute('title'))
            ->setDescription($model->getAttribute('description'))
            ->setBaseUrl(Url::to('/'))
            ->setGenerator('OctoberCMS/khalilthiero.RssFetcher')
            ->setId('khalilthiero.RssFecther.' . $model->getAttribute('id'))
            ->setLink(Url::to('/feeds/' . $path))
            ->setFeedLink(Url::to('/feeds/' . $path), $model->getAttribute('type'))
            ->setDateModified()
            ->addAuthor(['name' => 'OctoberCMS']);

    /** @var Collection $sources */
    $categories = $model->categories;
    $ids = Arr::pluck($categories->toArray(), 'id');
    $posts = [];
    $blogPostClass = 'RainLab\\Blog\\Models\\Post';
    $posts = $blogPostClass::where('published', '=', 1)
            ->whereDate('published_at', '<=', date('Y-m-d'))
            ->whereHas('categories', function($query)use ($ids) {
                $query->where('rainlab_blog_categories.id', $ids);
            })
            ->orderBy('published_at', 'desc')
            ->limit($model->getAttribute('max_items'))
            ->get();

    /** @var Post $post */
    foreach ($posts as $post) {
        try {
            $entry = new Entry();
            $postLink = url($post->getAttribute('slug'));
            $description = $post->getAttribute('content');
            $entry->setId((string) $post->getAttribute('id'))
                    ->setTitle($post->getAttribute('title'))
                    ->setDescription($description)
                    ->setLink($postLink)
                    ->setDateModified($post->getAttribute('published_at'));

//            $comments = $post->getAttribute('comments');
            $comments = null;
            if (!empty($comments)) {
                $entry->setCommentLink($comments);
            }

            $categories = $post->categories;

            if (!empty($categories)) {
                foreach ($categories as $category) {
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

            $feed->addEntry($entry);
        } catch (InvalidArgumentException $e) {
            continue;
        }
    }

    return Response::make($feed->export($model->getAttribute('type')), 200, [
                'Content-Type' => sprintf('application/%s+xml', $model->getAttribute('type')),
    ]);
});
