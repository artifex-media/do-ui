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
        $new_model = $this->model->replicate(['media']);
    
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
    
        // Get all relationships dynamically
        $relationships = $this->model->getRelations();
    
        foreach ($relationships as $relationship => $items) {
            foreach ($items as $item) {
                // Check if the related model can handle media
                if (method_exists($item, 'registerMediaConversions') || method_exists($item, 'addMedia')) {
                    $new_item = $item->replicate(['media']);
                } else {
                    $new_item = $item->replicate();
                }
    
                $foreign_key = $this->model->getForeignKey();
                $new_item->$foreign_key = $new_model->id;
                $new_item->save();
    
                if (method_exists($item, 'registerMediaConversions') || method_exists($item, 'addMedia')) {
                    if(!$item->media->isEmpty()) {
                        foreach ($item->media as $media) {
                            assert($media instanceof \Spatie\MediaLibrary\Models\Media);
                            $props = $media->toArray();
                            unset($props['id']);
                            $new_item->addMedia($media->getPath())
                                ->preservingOriginal()
                                ->withProperties($props)
                                ->toMediaCollection($media->collection_name);
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
