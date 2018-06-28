<?php

declare(strict_types = 1);

namespace Khalilthiero\RssFetcher\Models;

use Model;
use October\Rain\Database\Builder;
use Str;
use File;
use System\Models\File as FileModel;

/**
 * Class Item
 *
 * @package Khalilthiero\RssFetcher\Models
 */
class Item extends Model {

    /**
     * {@inheritdoc}
     */
    public $table = 'khalilthiero_rssfetcher_items';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'source_id',
        'item_id',
        'post_id',
        'title',
        'slug',
        'link',
        'description',
        'author',
        'tags',
        'comments',
        'enclosure_url',
        'enclosure_length',
        'enclosure_type',
        'pub_date',
        'is_published'
    ];

    /**
     * {@inheritdoc}
     */
    protected $dates = [
        'pub_date'
    ];

    /**
     * {@inheritdoc}
     */
    public $belongsTo = [
        'source' => Source::class
    ];
    /*
     * Validation
     */
    public $rules = [
        'slug' => 'required|between:3,64|unique:khalilthiero_rssfetcher_items',
    ];

    /**
     * Allows filtering for specifc sources
     *
     * @param Builder $query
     * @param array $sources List of source ids
     * @return Builder
     */
    public function scopeFilterSources(Builder $query, array $sources = []): Builder {
        return $query->whereHas('source', function ($q) use ($sources) {
                    $q->whereIn('id', $sources);
                });
    }
    public function beforeValidate() {
        // Generate a URL slug for this model
        if (!$this->exists && !$this->slug) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function publishToBlog($_publish = true) {
        // if its RainLab Blog or pro blog
        $blogCategoryClass = 'RainLab\\Blog\\Models\\Category';
        $blogPostClass = 'RainLab\\Blog\\Models\\Post';
        $blogTagClass = 'Bedard\\BlogTags\\Models\\Tag';
        $postBlog = $blogPostClass::where('slug', '=', Str::slug($this->title))->first();
        //If post doesn't exist then create
        if (!$postBlog) {
            $postBlog = $blogPostClass::create([
                        'title' => $this->title,
                        'slug' => Str::slug($this->title),
                        'content' => '&nbsp;']);
            // trying to get featured image
            if (stripos($this->enclosure_type, 'image') !== false) {
                $this->saveFeaturedImage($postBlog, $this->enclosure_url, $blogPostClass);
            }
        }
        //Get category tags
        foreach ($this->source->categories as $categoryModel) {
            //get category
            $category = trim($categoryModel->name);
            $postCategory = $blogCategoryClass::where(['name' => $category])->first();
            if (is_null($postCategory)) {
                continue;
            }
            //Detach if exist
            $postCategory->posts()->detach($postBlog->id);
            //Attach category to post
            $postCategory->posts()->attach($postBlog->id);
        }
        $tags = explode(',', $this->tags ?: '');
        foreach ($tags as $tag) {
            $tag = trim($tag);
            //Insert tag
            $postTag = $blogTagClass::firstOrCreate(['name' => $tag]);
            $postTag->slug = Str::slug($tag);
            $postTag->save();
            //Detach if exist
            $postBlog->tags()->detach($postTag->id);
            //Attach tags to post
            $postBlog->tags()->attach($postTag->id);
        }
        $postBlog->content = $this->description . '<br><br>' . $this->link;
        $postBlog->published = $_publish;
        $postBlog->published_at = $this->pub_date;
        $postBlog->save();

        $this->update(['is_published' => $_publish, 'post_id' => $postBlog->id]);
    }

    /**
     * Grab image from url
     * @param  string
     * @return array
     */
    private function downloadFileCurl($url) {
        set_time_limit(360);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $fileContent = curl_exec($ch);
        curl_close($ch);

        if ($fileContent) {
            return $fileContent;
        } else {
            return false;
        }
    }

    /**
     * Generate hashed folder name from filename
     *
     * @param  string
     * @return array
     */
    private function generateHashedFolderName($filename) {
        $folderName[] = substr($filename, 0, 3);
        $folderName[] = substr($filename, 3, 3);
        $folderName[] = substr($filename, 6, 3);
        return $folderName;
    }

    private function saveFeaturedImage($_postBlog, $_featuredImageUrl, $_blogPostClass) {
        $tempFolder = 'storage/app/uploads/public/';
        $fileContents = $this->downloadFileCurl($_featuredImageUrl);
        if ($fileContents) {
            $fileName = basename($_featuredImageUrl);
            $fileExt = File::extension($_featuredImageUrl);

            $hash = md5($fileName . '!' . str_random(40)); //need to randomize filename incase file exists
            $diskName = base64_encode($fileName . '!' . $hash) . '.' . $fileExt;
            //Write it to temp storage
            $fileTemp = $tempFolder . $diskName;
            File::put($fileTemp, $fileContents);
            $uploadFolders = $this->generateHashedFolderName($diskName);
            $uploadFolder = $tempFolder . $uploadFolders[0] . '/' . $uploadFolders[1] . '/' . $uploadFolders[2];
            File::makeDirectory($uploadFolder, 0755, true, true);

            $fileMime = File::mimeType($fileTemp);
            $fileSize = File::size($fileTemp);

            $fileNew = $uploadFolder . '/' . $diskName;
            if (File:: move($fileTemp, $fileNew)) {
                //Save to db
                $postFeaturedImage = new FileModel;
                $postFeaturedImage->disk_name = $diskName;
                $postFeaturedImage->file_name = $fileName;
                $postFeaturedImage->file_size = $fileSize;
                $postFeaturedImage->content_type = $fileMime;
                $postFeaturedImage->field = 'featured_images';
                $postFeaturedImage->attachment_id = $_postBlog->id;
                $postFeaturedImage->attachment_type = $_blogPostClass;
                $postFeaturedImage->is_public = 1;
                $postFeaturedImage->sort_order = 1;
                $postFeaturedImage->save();
            }
        }
    }

}
