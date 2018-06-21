<?php

declare(strict_types = 1);

namespace Khalilthiero\RssFetcher\Controllers;

use Khalilthiero\RssFetcher\Models\Item;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use BackendMenu;
use Backend\Classes\Controller;
use Exception;
use Str;
use File;
use System\Models\File as FileModel;

/**
 * Class Items
 *
 * @package Khalilthiero\RssFetcher\Controllers
 * @mixin FormController
 * @mixin ListController
 */
class Items extends Controller {

    /**
     * {@inheritdoc}
     */
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    /**
     * @var string
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string
     */
    public $listConfig = 'config_list.yaml';

    /**
     * {@inheritdoc}
     */
    public function __construct() {
        parent::__construct();

        BackendMenu::setContext('khalilthiero.RssFetcher', 'rssfetcher', 'items');
    }

    // @codingStandardsIgnoreStart

    /**
     * @return array
     * @throws Exception
     */
    public function index_onDelete(): array {
        foreach ($this->getCheckedIds() as $sourceId) {
            if (!$source = Item::find($sourceId)) {
                continue;
            }

            $source->delete();
        }

        return $this->listRefresh();
    }

    /**
     * @return array
     */
    public function index_onPublish() {
        return $this->publishItem(true);
    }

    /**
     * @return array
     */
    public function index_onUnpublish() {
        return $this->publishItem(false);
    }

    // @codingStandardsIgnoreEnd

    /**
     * @param $publish
     * @return array
     */
    private function publishItem($publish): array {
        foreach ($this->getCheckedIds() as $sourceId) {
            if (!$item = Item::find($sourceId)) {
                continue;
            }
            // if its RainLab Blog or pro blog
            $blogCategoryClass = 'RainLab\\Blog\\Models\\Category';
            $blogPostClass = 'RainLab\\Blog\\Models\\Post';
            $blogTagClass = 'Bedard\\BlogTags\\Models\\Tag';
            $postBlog = $blogPostClass::where('title', '=', $item->title)->first();
            //If post doesn't exist then create (to avoid rules for blog plugin)
            if (!$postBlog) {
                $postBlog = $blogPostClass::create([
                            'title' => $item->title,
                            'slug' => Str::slug($item->title),
                            'content' => '&nbsp;']);
                // trying to get featured image
                if (stripos($item->enclosure_type, 'image')!==false) {
                    $this->saveFeaturedImage($postBlog, $item->enclosure_url, $blogPostClass);
                }
            }
            //Get category tags
            $categories = explode(',', $item->source->category);
            foreach ($categories as $category) {
                //get category
                $category = trim($category);
                $postCategory = $blogCategoryClass::where(['name' => $category])->first();
                if (is_null($postCategory)) {
                    continue;
                }
                //Detach if exist
                $postCategory->posts()->detach($postBlog->id);
                //Attach category to post
                $postCategory->posts()->attach($postBlog->id);
            }
            $tags = explode(',', $item->tags ?: '');
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
            $postBlog->content = $item->description . '<br><br>' . $item->link;
            $postBlog->published = $publish;
            $postBlog->published_at = $item->pub_date;
            $postBlog->save();

            $item->update(['is_published' => $publish, 'post_id' => $postBlog->id]);
        }

        return $this->listRefresh();
    }

    /**
     * Check checked ID's from POST request.
     *
     * @return array
     */
    private function getCheckedIds(): array {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)
        ) {
            return $checkedIds;
        }

        return [];
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
