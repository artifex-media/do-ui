<?php

namespace Doorons\DoUI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;

// Ajax Only controller!
class ResourceController extends Controller
{

    private $resource_request,$model, $model_type, $model_id, $newPosition, $modelImageCollection, $action;

    public function __construct(Request $request) {
        $this->model_type = $request->get('model');
        $this->model_id = $request->get('id');
        $this->action = $request->get('action');
        $this->newPosition = $request->get('newPosition');
        $this->modelImageCollection = $request->get('modelImageCollection');
        $this->model = $this->action == 'restore' ? $this->model_type::withTrashed()->find($this->model_id) : $this->model_type::find($this->model_id);
        $this->resource_request = $request;
    }

    public function parse(Request $request) {
        return (new ResourceController($request))->{$this->action}();
    }

    public function delete() {
        $result = $this->model->delete();

        return $result ? 'true' : 'false';
    }

    public function restore() {
        $result = $this->model->restore();

        return $result ? 'true' : 'false';
    }

    public function archive() {
        $this->model->archive = 1;
        $result = $this->model->save();

        return $result ? 'true' : 'false';
    }

    public function unarchive() {
        $this->model->archive = 0;
        $result = $this->model->save();

        return $result ? 'true' : 'false';
    }

    public function duplicate()
    {
        
        if (method_exists($this->model, 'registerMediaConversions') || method_exists($this->model, 'addMedia')) {
            $new_model = $this->model->replicate(['media']);
        } else {
            $new_model = $this->model->replicate();
        }
    
        $new_model->title = str_replace(' (duplicate)', '', $new_model->title) . ' (duplicate)';
        $new_model->status = is_int($this->model->status) ? 0 : 'inactive';
    
        if (Schema::hasColumn($new_model->getTable(), 'uuid')) {
            $new_model->uuid = Str::uuid();
        }
    
        if (Schema::hasColumn($new_model->getTable(), 'slug')) {
            $new_model->slug = $this->model->slug . '-dup-' . $this->model->where('archive', 0)->count();
        }
    
        if (Schema::hasColumn($new_model->getTable(), 'permalink')) {
            $new_model->permalink = 'https://duplicate-' . $this->model->where('archive', 0)->count() . '.nl';
        }
    
        if (Schema::hasColumn($new_model->getTable(), 'custom_domain')) {
            $new_model->custom_domain = 'https://duplicate-' . $this->model->where('archive', 0)->count() . '.nl';
        }
    
        $new_model->push();
        $new_model->save();

        $relationships = ['fields','faqs', 'deals', 'tags','rewards', 'brands', 'childs','pages','menus','links','settings','packages','blocks','partners','incentives'];
        foreach ($relationships as $relationship) {

            $foreign = substr_replace($relationship,"",-1).'_id';
            $foreign_this = substr_replace($this->model->getTable(),"",-1).'_id';


            if (method_exists($this->model, $relationship) && $this->model->$relationship()->exists() && count($this->model->$relationship)) {
                foreach($this->model->$relationship as $item) {

                    // Check if the related model can handle media
                    if (method_exists($item, 'registerMediaConversions') || method_exists($item, 'addMedia')) {
                        $new_item = $item->replicate(['media']);
                    } else {
                        $new_item = $item->replicate();
                    }
        
                    $foreign_key = $this->model->getForeignKey();
                    $new_item->$foreign_key = $new_model->id;

                    if (Schema::hasColumn($new_item->getTable(), 'uuid')) {
                        $new_item->uuid = Str::uuid();
                    }

                    $new_item->save();

                    // Duplicate meta fields for related model if using Metable
                    if (in_array('Zoha\\Metable', class_uses($item))) {
                        $meta = $item->meta()->get();
                        foreach ($meta as $metaItem) {
                            $new_item->setMeta($metaItem->key, $metaItem->value);
                        }
                    }
        
                    // Check if the related model has media and duplicate if applicable
                    if (method_exists($item, 'registerMediaConversions') || method_exists($item, 'addMedia')) {
                        if (!$item->media->isEmpty()) {
                            foreach ($item->media as $media) {
                                // Check if the media is an instance of either class
                                if (!($media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media || $media instanceof \Spatie\MediaLibrary\Models\Media)) {
                                    throw new \Exception("Unexpected media type: " . get_class($media));
                                }
                    
                                $path = $media->getPath();
                    
                                if (\Illuminate\Support\Facades\File::exists($path)) {
                                    $props = $media->toArray();
                    
                                    // Remove non-existent fields and the uuid to prevent duplicate entries
                                    unset($props['id'], $props['uuid'], $props['original_url'], $props['preview_url']);
                    
                                    $new_item->addMedia($media->getPath())
                                        ->preservingOriginal()
                                        ->withProperties($props)
                                        ->toMediaCollection($media->collection_name);
                                }
                            }
                        }
                    }
                    
                    
                }
            }
        }
    
        if ($this->model->cats) {
            foreach ($this->model->cats as $cat) {
                $new_model->cats()->attach($cat);
            }
        }

        if (method_exists($this->model, 'registerMediaConversions') || method_exists($this->model, 'addMedia')) {
            if (!$this->model->media->isEmpty()) {
                foreach ($this->model->media as $model_media) {
                    // Check if media file exists physically

                    $model_path = $model_media->getPath();
                    if (\Illuminate\Support\Facades\File::exists($model_path)) {
                        $model_props = $model_media->toArray();
                        unset($model_props['id']);
                        $new_model->addMedia($model_media->getPath())
                            ->preservingOriginal()
                            ->withProperties($model_props)
                            ->toMediaCollection($model_media->collection_name);
                    }
                }
            }
        }
    
        \Session::flash('success-message', 'Successfully duplicated!');
    
        return $new_model->exists ? 'true' : 'false';
    }

    public function reposition()
    {
        $this->model->position  = $this->newPosition;
        $result = $this->model->save();

        return $result ? 'true' : 'false';
    }


    public function deletemedia()
    {
        $this->model->clearMediaCollection($this->modelImageCollection);
        $result = $this->model->save();

        return $result ? 'true' : 'false';
    }
           
}
